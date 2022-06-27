<?php
/*
 * This file is part of CLAIRE.
 *
 * CLAIRE is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * CLAIRE is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with CLAIRE. If not, see <http://www.gnu.org/licenses/>
 */

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\TextWithHoles;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\CommonItem;

/**
 * Class Exercise
 *
 * @author Valentin Lachand-Pascal <valentin.lachand@liris.cnrs.fr>
 */
class Item extends CommonItem
{
    /**
     * text:
     *
     * @var string $text
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    private $text = "";

    /**
     * bold:
     *
     * @var array $bold
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    private $bold = Array();

    /**
     * @var int $itemId Id of item
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "exercise", "list", "corrected", "not_corrected"})
     */
    private $itemId;

    /**
     * italize:
     *
     * @var array $italize
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    private $italize = Array();

    /**
     * underline:
     *
     * @var array $underline
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    private $underline = Array();

    /**
     * answers:
     *
     * @var array $holes
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    private $holes = Array();

    /**
     * answers:
     *
     * @var array $answers
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    private $answers = Array();

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * @return array
     */
    public function getBold(): array
    {
        return $this->bold;
    }

    /**
     * @param array $bold
     */
    public function setBold(array $bold): void
    {
        $this->bold = $bold;
    }

    /**
     * @return array
     */
    public function getItalize(): array
    {
        return $this->italize;
    }

    /**
     * @param array $italize
     */
    public function setItalize(array $italize): void
    {
        $this->italize = $italize;
    }

    /**
     * @return array
     */
    public function getUnderline(): array
    {
        return $this->underline;
    }

    /**
     * @param array $underline
     */
    public function setUnderline(array $underline): void
    {
        $this->underline = $underline;
    }

    /**
     * @return array
     */
    public function getHoles(): array
    {
        return $this->holes;
    }

    /**
     * @param array $holes
     */
    public function setHoles(array $holes): void
    {
        $this->holes = $holes;
    }

    /**
     * @return int
     */
    public function getItemId(): int
    {
        return $this->itemId;
    }

    /**
     * @param int $itemId
     */
    public function setItemId(int $itemId): void
    {
        $this->itemId = $itemId;
    }

    /**
     * @return array
     */
    public function getAnswers(): array
    {
        return $this->answers;
    }

    /**
     * @param array $answers
     */
    public function setAnswers(array $answers): void
    {
        $this->answers = $answers;
    }

}
