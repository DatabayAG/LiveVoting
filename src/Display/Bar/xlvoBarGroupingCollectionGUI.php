<?php

declare(strict_types=1);

namespace LiveVoting\Display\Bar;

use ilException;

/**
 * The grouping collection groups elements by the freeinput text and shows an corresponding badge to
 * indicate the number of times the answer got submitted by the voters.
 *
 * Please note that this class is only compatible with the xlvoBarFreeInputsGUI bar type.
 */
final class xlvoBarGroupingCollectionGUI extends xlvoBarCollectionGUI
{
    public const TEMPLATE_BLOCK_NAME = 'bar';
    /** @var xlvoBarFreeInputsGUI[] $bars */
    private array $bars = [];
    private bool $removable = false;
    private bool $rendered = false;
    private bool $sorted = false;

    /**
     * @throws ilException If the bars are already rendered or the given type is not compatible
     *                     with the collection.
     */
    public function addBar(xlvoGeneralBarGUI $bar_gui): void
    {
        $this->checkCollectionState();

        if ($bar_gui instanceof xlvoBarFreeInputsGUI) {
            $bar_gui->setRemovable($this->isRemovable());
            $this->bars[] = $bar_gui;
        } else {
            throw new ilException('$bar_gui must a type of xlvoBarFreeInputsGUI.');
        }
    }

    /**
     * @throws ilException If the bars are already rendered.
     */
    private function checkCollectionState(): void
    {
        if ($this->rendered) {
            throw new ilException(
                "The bars are already rendered, therefore the collection can't be modified or rendered."
            );
        }
    }

    public function isRemovable(): bool
    {
        return $this->removable;
    }

    public function setRemovable(bool $removable): void
    {
        $this->removable = $removable;
    }

    public function sorted(bool $enabled): void
    {
        $this->sorted = $enabled;
    }

    /**
     * @throws ilException If the bars are already rendered.
     */
    public function getHTML(): string
    {
        $this->checkCollectionState();

        $this->renderVotersAndVotes();

        $bars = null;
        if ($this->sorted) {
            $bars = $this->sortBarsByFrequency($this->bars);
        } else {
            $bars = $this->makeUniqueArray($this->bars);
        }

        //render the bars on demand
        foreach ($bars as $bar) {
            $count = $this->countItemOccurence($this->bars, $bar);
            $this->renderBar($bar, $count);
        }

        if (count($this->bars) === 0) {
            $this->tpl->touchBlock('bar');
        }
        unset($this->bars);
        $this->rendered = true;

        return $this->tpl->get();
    }

    /**
     * Creates a copy with unique elements of the supplied array and sorts the content afterwards.
     * The current sorting is descending.
     *
     * @param xlvoBarFreeInputsGUI[] $bars The array of bars which should be sorted.
     * @return xlvoBarFreeInputsGUI[] Descending sorted array.
     */
    private function sortBarsByFrequency(array $bars): array
    {
        //dirty -> should be optimised in the future.

        $unique = $this->makeUniqueArray($bars);

        //[[count, bar], [count, bar]]
        $result = [];

        foreach ($unique as $item) {
            $result[] = [$this->countItemOccurence($bars, $item), $item];
        }

        //sort elements
        usort($result, static function (array $array1, array $array2) {
            if ($array1[0] === $array2[0]) {
                return 0;
            }

            if ($array1[0] < $array2[0]) {
                return 1;
            }

            return -1;
        });

        //flatten the array to the bars
        $sortedResult = [];

        foreach ($result as $entry) {
            $sortedResult[] = $entry[1];
        }

        return $sortedResult;
    }

    /**
     * Filter the array by freetext input.
     * The filter is case insensitive.
     *
     * @param xlvoBarFreeInputsGUI[] $bars The array which should be filtered.
     * @return xlvoBarFreeInputsGUI[] The new array which contains only unique bars.
     */
    private function makeUniqueArray(array $bars): array
    {
        /**
         * @var xlvoBarFreeInputsGUI $filter
         */
        $uniqueBars = [];

        while (count($bars) > 0) {
            $bar = reset($bars);
            $bars = array_filter($bars, static function ($item) use ($bar) {
                return !$bar->equals($item);
            });
            $uniqueBars[] = $bar;
        }

        return $uniqueBars;
    }

    /**
     * Count the occurrences of bar within the given collection of bar.
     *
     * @param xlvoBarFreeInputsGUI[] $bars The collection which should be searched
     */
    private function countItemOccurence(array $bars, xlvoBarFreeInputsGUI $bar): int
    {
        $count = 0;
        foreach ($bars as $entry) {
            if ($bar->equals($entry)) {
                $count++;
            }
        }

        return $count;
    }

    private function renderBar(xlvoBarFreeInputsGUI $bar, int $count): void
    {
        $bar->setOccurrences($count);

        $this->tpl->setCurrentBlock(self::TEMPLATE_BLOCK_NAME);
        $this->tpl->setVariable('BAR', $bar->getHTML());
        $this->tpl->parseCurrentBlock();
    }

    /**
     * @throws ilException If the bars are already rendered.
     */
    public function addSolution(string $html): void
    {
        $this->checkCollectionState();
        parent::addSolution($html);
    }

    /**
     * @throws ilException If the bars are already rendered.
     */
    public function setTotalVotes($total_votes): void
    {
        $this->checkCollectionState();
        parent::setTotalVotes($total_votes);
    }

    /**
     * @throws ilException If the bars are already rendered.
     */
    public function setShowTotalVotes(bool $show_total_votes): void
    {
        $this->checkCollectionState();
        parent::setShowTotalVotes($show_total_votes);
    }

    /**
     * @throws ilException If the bars are already rendered.
     */
    public function setTotalVoters(int $total_voters): void
    {
        $this->checkCollectionState();
        parent::setTotalVoters($total_voters);
    }

    /**
     * @throws ilException If the bars are already rendered.
     */
    public function setShowTotalVoters(bool $show_total_voters): void
    {
        $this->checkCollectionState();
        parent::setShowTotalVoters($show_total_voters);
    }
}
