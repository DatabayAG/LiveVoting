<?php

declare(strict_types=1);

namespace LiveVoting\PowerPointExport;

use ilLink;
use ilLiveVotingPlugin;
use ilObjLiveVoting;
use ilUtil;
use LiveVoting\QuestionTypes\xlvoQuestionTypes;
use LiveVoting\Utils\LiveVotingTrait;
use LiveVoting\Voting\xlvoVoting;
use LiveVoting\Voting\xlvoVotingConfig;
use srag\DIC\LiveVoting\DICTrait;
use ilShellUtil;
use ilFileUtils;
use ilFileDelivery;

class PowerPointExport
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    protected ilObjLiveVoting $obj;
    protected xlvoVotingConfig $config;
    protected string $temp_folder;
    protected string $temp_file;
    protected string $file_name;
    /** @var xlvoVoting[] */
    protected array $votings;

    public function __construct(ilObjLiveVoting $obj)
    {
        $this->obj = $obj;
        $this->config = xlvoVotingConfig::find($this->obj->getId());

        $this->temp_folder = $this->getTempFolder();
        $this->temp_file = $this->temp_folder . ".pptx";
        $this->file_name = $this->getFileName();
    }

    protected function getTempFolder(): string
    {
        $temp_directory = CLIENT_DATA_DIR . "/temp";
        //create it, if this plugin is the first one who uses it.
        self::dic()->filesystem()->storage()->createDir($temp_directory);

        return $temp_directory . "/" . uniqid(ilLiveVotingPlugin::PLUGIN_ID . "_pp_", true);
    }

    protected function getFileName(): string
    {
        return $this->obj->getTitle() . ".pptx";
    }

    public function run(): void
    {
        $this->loadVotings();

        $this->copyTemplate();

        $this->setDocumentProperties();

        $this->addVotings();

        $this->zip();

        $this->deliver();
    }

    protected function loadVotings(): void
    {
        $this->votings = array_values(
            xlvoVoting::where([
                "obj_id" => $this->obj->getId(),
                "voting_type" => xlvoQuestionTypes::getActiveTypes(),
            ])->orderBy("position", "ASC")->get()
        ); // Correct index with array_values
    }

    protected function copyTemplate(): void
    {
        // 	ilUtil::rCopy does not copy empty folders and rename unsecured extensions like .rels, so use simply UNIX copy
        ilShellUtil::execQuoted(
            'cp -r "' . __DIR__ . '/../../templates/PowerPointExport/pptx" "' . $this->temp_folder . '"'
        );
    }

    protected function setDocumentProperties(): void
    {
        $core_tpl = self::plugin()->template($this->temp_folder . "/docProps/core.xml", false, true, false);

        $core_tpl->setVariable("TITLE", htmlspecialchars($this->obj->getTitle()));

        $core_tpl->setVariable("DESCRIPTION", htmlspecialchars($this->obj->getDescription()));

        $core_tpl->setVariable("SUBJECT", htmlspecialchars(self::plugin()->translate("obj_xlvo")));

        $core_tpl->setVariable("CREATED", gmdate("Y-m-d\TH:i:s\Z", strtotime($this->obj->getCreateDate())));

        $core_tpl->setVariable("MODIFIED", gmdate("Y-m-d\TH:i:s\Z", strtotime($this->obj->getLastUpdateDate())));

        file_put_contents($this->temp_folder . "/docProps/core.xml", $core_tpl->get());

        $app_tpl = self::plugin()->template($this->temp_folder . "/docProps/app.xml", false, true, false);

        $app_tpl->setVariable("COMPANY", htmlspecialchars(ILIAS_HTTP_PATH));

        $app_tpl->setVariable("SLIDES", count($this->votings));
        $app_tpl->setVariable("NOTES", count($this->votings));
        $app_tpl->setVariable("PARAGRAPHS", (count($this->votings) * 2));

        file_put_contents($this->temp_folder . "/docProps/app.xml", $app_tpl->get());
    }

    protected function addVotings(): void
    {
        $this->updateContentTypesXML();

        $this->updatePresentationXML();

        $this->updateSlideXML();

        $this->updateWebExtensionXML();

        $this->updateNoteXML();
    }

    protected function updateContentTypesXML(): void
    {
        $core_types_tpl = self::plugin()->template($this->temp_folder . "/[Content_Types].xml", false, true, false);

        $core_types_tpl->setCurrentBlock("slide");
        foreach ($this->votings as $i => $voting) {
            $num = ($i + 1);

            $core_types_tpl->setVariable("NUM", $num);

            $core_types_tpl->parseCurrentBlock();
        }

        file_put_contents($this->temp_folder . "/[Content_Types].xml", $core_types_tpl->get());
    }

    protected function updatePresentationXML(): void
    {
        $presentation_tpl = self::plugin()->template($this->temp_folder . "/ppt/presentation.xml", false, true, false);
        $presentation_rels_tpl = self::plugin()->template(
            $this->temp_folder . "/ppt/_rels/presentation.xml.rels",
            false,
            true,
            false
        );

        $presentation_tpl->setCurrentBlock("slide");
        $presentation_rels_tpl->setCurrentBlock("slide");
        foreach ($this->votings as $i => $voting) {
            $num = ($i + 1);

            $rid = (7 + $i);

            $id = (256 + $i);

            $presentation_tpl->setVariable("NUM", $num);
            $presentation_rels_tpl->setVariable("NUM", $num);

            $presentation_tpl->setVariable("RID", $rid);
            $presentation_rels_tpl->setVariable("RID", $rid);

            $presentation_tpl->setVariable("ID", $id);

            $presentation_tpl->parseCurrentBlock();
            $presentation_rels_tpl->parseCurrentBlock();
        }

        file_put_contents($this->temp_folder . "/ppt/presentation.xml", $presentation_tpl->get());
        file_put_contents($this->temp_folder . "/ppt/_rels/presentation.xml.rels", $presentation_rels_tpl->get());
    }

    protected function updateSlideXML(): void
    {
        foreach ($this->votings as $i => $voting) {
            $num = ($i + 1);
            $num2 = ($num * 2);
            $num3 = ($num2 + 1);

            $guid = $this->guid();

            $title = $voting->getTitle();
            $question = strip_tags($voting->getQuestion());

            $slide_tpl = self::plugin()->template($this->temp_folder . "/ppt/slides/slide.xml", false, true, false);
            $slide_rels_tpl = self::plugin()->template(
                $this->temp_folder . "/ppt/slides/_rels/slide.xml.rels",
                false,
                true,
                false
            );

            $slide_tpl->setVariable("NUM", $num);
            $slide_rels_tpl->setVariable("NUM", $num);

            $slide_tpl->setVariable("NUM2", $num2);
            $slide_tpl->setVariable("NUM3", $num3);

            $slide_tpl->setVariable("GUID", $guid);

            $slide_tpl->setVariable("TITLE", htmlspecialchars($title));

            $slide_tpl->setVariable("QUESTION", htmlspecialchars($question));

            file_put_contents($this->temp_folder . "/ppt/slides/slide$num.xml", $slide_tpl->get());
            file_put_contents($this->temp_folder . "/ppt/slides/_rels/slide$num.xml.rels", $slide_rels_tpl->get());

            copy($this->temp_folder . "/ppt/media/image.png", $this->temp_folder . "/ppt/media/image$num.png");
        }

        unlink($this->temp_folder . "/ppt/slides/slide.xml");
        unlink($this->temp_folder . "/ppt/slides/_rels/slide.xml.rels");
        unlink($this->temp_folder . "/ppt/media/image.png");
    }

    protected function guid(): string
    {
        if (function_exists('com_create_guid')) {
            return "{" . trim(com_create_guid(), '{}') . "}";
        }

        return "{"
            . sprintf(
                '%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
                random_int(0, 65535),
                random_int(0, 65535),
                random_int(0, 65535),
                random_int(16384, 20479),
                random_int(32768, 49151),
                random_int(0, 65535),
                random_int(0, 65535),
                random_int(0, 65535)
            )
            . "}";
    }

    protected function updateWebExtensionXML(): void
    {
        foreach ($this->votings as $i => $voting) {
            $num = ($i + 1);

            $guid = $this->guid();

            $presenter_link = $this->config->getPresenterLink($voting->getId(), true, true, false);

            $webextension_tpl = self::plugin()->template(
                $this->temp_folder . "/ppt/webextensions/webextension.xml",
                false,
                true,
                false
            );
            $webextension_rels_tpl = self::plugin()->template(
                $this->temp_folder
                . "/ppt/webextensions/_rels/webextension.xml.rels",
                false,
                true,
                false
            );

            $webextension_tpl->setVariable("NUM", $num);
            $webextension_rels_tpl->setVariable("NUM", $num);

            $webextension_tpl->setVariable("GUID", $guid);

            $webextension_tpl->setVariable("LINK", htmlspecialchars($presenter_link));

            $webextension_tpl->setVariable("SECURE", var_export(true, true));

            file_put_contents(
                $this->temp_folder . "/ppt/webextensions/webextension$num.xml",
                $webextension_tpl->get()
            );
            file_put_contents(
                $this->temp_folder . "/ppt/webextensions/_rels/webextension$num.xml.rels",
                $webextension_rels_tpl->get()
            );
        }

        unlink($this->temp_folder . "/ppt/webextensions/webextension.xml");
        unlink($this->temp_folder . "/ppt/webextensions/_rels/webextension.xml.rels");
    }

    protected function updateNoteXML(): void
    {
        foreach ($this->votings as $i => $voting) {
            $num = ($i + 1);

            $data = [
                "voting_title" => $voting->getTitle(),
                "voting_question" => strip_tags($voting->getQuestion()),
                "empty1" => "",
                "voting_short_link" => $this->config->getShortLinkURL(true, $this->obj->getRefId()),
                "voting_permanent_link" => ilLink::_getStaticLink($this->obj->getRefId(), $this->obj->getType()),
                "empty2" => ""
            ];

            $note = implode(
                "\n",
                array_map(static function (string $txt, string $value) {
                    if ($txt !== "" && $value !== "") {
                        return self::plugin()->translate($txt) . ": " . $value;
                    }
                    return "";
                }, array_keys($data), $data)
            );

            $notesslide_tpl = self::plugin()->template(
                $this->temp_folder . "/ppt/notesSlides/notesSlide.xml",
                false,
                true,
                false
            );
            $notesslide_rels_tpl = self::plugin()->template(
                $this->temp_folder . "/ppt/notesSlides/_rels/notesSlide.xml.rels",
                false,
                true,
                false
            );

            $notesslide_tpl->setVariable("NUM", $num);
            $notesslide_rels_tpl->setVariable("NUM", $num);

            $notesslide_tpl->setVariable("NOTE", htmlspecialchars($note));

            file_put_contents($this->temp_folder . "/ppt/notesSlides/notesSlide$num.xml", $notesslide_tpl->get());
            file_put_contents(
                $this->temp_folder . "/ppt/notesSlides/_rels/notesSlide$num.xml.rels",
                $notesslide_rels_tpl->get()
            );
        }

        unlink($this->temp_folder . "/ppt/notesSlides/notesSlide.xml");
        unlink($this->temp_folder . "/ppt/notesSlides/_rels/notesSlide.xml.rels");
    }

    protected function zip(): void
    {
        ilFileUtils::zip($this->temp_folder, $this->temp_file, true);

        ilFileUtils::delDir($this->temp_folder);
    }

    protected function deliver(): void
    {
        ilFileDelivery::deliverFileLegacy($this->temp_file, $this->file_name, "", false, true, true);
    }
}
