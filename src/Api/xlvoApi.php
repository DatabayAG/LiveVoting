<?php

declare(strict_types=1);

namespace LiveVoting\Api;

use DOMCdataSection;
use DOMDocument;
use DOMElement;
use ilLiveVotingPlugin;
use ilObject2;
use LiveVoting\Conf\xlvoConf;
use LiveVoting\Exceptions\xlvoPlayerException;
use LiveVoting\Pin\xlvoPin;
use LiveVoting\Results\xlvoResults;
use LiveVoting\Round\xlvoRound;
use LiveVoting\Utils\LiveVotingTrait;
use LiveVoting\Voting\xlvoVotingManager2;
use srag\DIC\LiveVoting\DICTrait;
use stdClass;
use DOMNode;

/**
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class xlvoApi
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    public const TYPE_JSON = 1;
    public const TYPE_XML = 2;
    protected int $type = self::TYPE_XML;
    protected string $token = '';
    protected xlvoPin $pin;
    protected stdClass $data;

    public function __construct(xlvoPin $pin, string $token)
    {
        $this->pin = $pin;
        $this->token = $token;
        $this->initType();
        $this->check();

        $manager = new xlvoVotingManager2($this->pin->getPin());
        $title = ilObject2::_lookupTitle($manager->getObjId());
        $data = new stdClass();
        $data->Info = new stdClass();
        $data->Info->Title = $title;
        $latestRound = xlvoRound::getLatestRound($manager->getObjId());
        $data->Info->Round = $latestRound->getRoundNumber();
        $data->Info->RoundId = $latestRound->getId();
        $data->Info->Pin = $pin->getPin();
        $data->Info->Date = date(DATE_ATOM);
        $data->Votings = [];

        $xlvoResults = new xlvoResults($manager->getObjId(), $latestRound->getId());

        foreach ($manager->getAllVotings() as $xlvoVoting) {
            $stdClass = $xlvoVoting->_toJson();
            $stdClass->Voters = [];

            foreach ($xlvoResults->getData(['voting' => $xlvoVoting->getId()]) as $item) {
                $Voter = new stdClass();
                $Voter->Identifier = $item['participant'];
                $Voter->AnswerIds = $item['answer_ids'];
                $Voter->AnswerText = $item['answer'];

                $stdClass->Voters[] = $Voter;
            }

            $data->Votings[$xlvoVoting->getPosition()] = $stdClass;
        }

        $this->data = $data;
    }

    protected function initType(): void
    {
        $type = xlvoConf::getConfig(xlvoConf::F_API_TYPE);
        $this->setType($type ?: self::TYPE_JSON);
    }

    protected function check(): void
    {
        xlvoPin::checkPinAndGetObjId($this->getPin()->getPin());

        if (!xlvoConf::getConfig(xlvoConf::F_RESULT_API)) {
            throw new xlvoPlayerException('API not configured', 3);
        }
        if ($this->getToken() !== xlvoConf::getApiToken()) {
            throw new xlvoPlayerException('wrong API token', 4);
        }
    }

    public function getPin(): xlvoPin
    {
        return $this->pin;
    }

    public function setPin(xlvoPin $pin): void
    {
        $this->pin = $pin;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function send(): void
    {
        switch ($this->type) {
            case self::TYPE_JSON:
                header('Content-Type: application/json');
                echo json_encode($this->data, JSON_THROW_ON_ERROR);
                break;
            case self::TYPE_XML:
                $domxml = new DOMDocument('1.0', 'UTF-8');
                $domxml->preserveWhiteSpace = false;
                $domxml->formatOutput = true;
                $this->appendXMLElement($domxml, 'LiveVotingResults', $this->data);

                header('Content-Type: application/xml');
                echo $domxml->saveXML();
                break;
        }
    }

    /**
     * @param DOMElement|DOMNode   $dom
     * @param                      $key
     * @param                      $data
     *
     * @return DOMElement|DOMNode
     */
    protected function appendXMLElement($dom, string $key, $data)
    {
        $return = $dom;
        switch (true) {
            case ($data instanceof stdClass):
                $newdom = $dom->appendChild(new DOMElement($key));
                foreach ($data as $k => $v) {
                    $this->appendXMLElement($newdom, $k, $v);
                }
                break;
            case (is_array($data)):
                $newdom = $dom->appendChild(new DOMElement($key));
                foreach ($data as $k => $v) {
                    $this->appendXMLElement($newdom, rtrim($key, "s"), $v);
                }
                break;
            default:
                $dom->appendChild(new DOMElement($key))->appendChild(new DOMCdataSection($data));
                break;
        }

        return $return;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public function getData(): stdClass
    {
        return $this->data;
    }

    public function setData(stdClass $data): void
    {
        $this->data = $data;
    }
}
