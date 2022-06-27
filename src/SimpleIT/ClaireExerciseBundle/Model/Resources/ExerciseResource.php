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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\CommonExercise;

/**
 * Class ExerciseModelResource
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ExerciseResource
{
    /**
     * @const RESOURCE_NAME = 'Exercise'
     */
    const RESOURCE_NAME = 'Exercise';

    /**
     * @const MULTIPLE_CHOICE_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\MultipleChoice\Exercise'
     */
    const MULTIPLE_CHOICE_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\MultipleChoice\Exercise';

    /**
     * @const GROUP_ITEMS_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\GroupItems\Exercise'
     */
    const GROUP_ITEMS_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\GroupItems\Exercise';

    /**
     * @const GROUP_ITEMS_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\GroupItems\Exercise'
     */
    const ORDER_ITEMS_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\OrderItems\Exercise';

    /**
     * @const PAIR_ITEMS_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\PairItems\Exercise'
     */
    const PAIR_ITEMS_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\PairItems\Exercise';

    /**
     * @const OPEN_ENDED_QUESTION_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\OpenEndedQuestion\Exercise'
     */
    const OPEN_ENDED_QUESTION_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\OpenEndedQuestion\Exercise';

    /**
     * @const TEXT_WITH_HOLES_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\TextWithHoles\Exercise'
     */
    const TEXT_WITH_HOLES_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\TextWithHoles\Exercise';

    /**
     * @var int $id Id of exercise
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "exercise", "list"})
     */
    private $id;

    /**
     * @var int $exerciseModel
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "exercise", "list"})
     */
    private $exerciseModel;

    /**
     * @var string $type
     * @Serializer\Exclude
     */
    private $type;

    /**
     * @var CommonExercise $content
     * @Serializer\Type("SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\CommonExercise")
     * @Serializer\Groups({"details", "exercise"})
     */
    private $content;

    /**
     * @var string $title
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "exercise"})
     */
    private $title;

    /**
     * @var array
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\AttemptResource>")
     * @Serializer\Groups({"details"})
     */
    private $attempts;

    /**
     * Set content
     *
     * @param \SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\CommonExercise $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return \SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\CommonExercise
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set exerciseModel
     *
     * @param int $exerciseModel
     */
    public function setExerciseModel($exerciseModel)
    {
        $this->exerciseModel = $exerciseModel;
    }

    /**
     * Get exerciseModel
     *
     * @return int
     */
    public function getExerciseModel()
    {
        return $this->exerciseModel;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set attempts
     *
     * @param array $attempts
     */
    public function setAttempts($attempts)
    {
        $this->attempts = $attempts;
    }

    /**
     * Get attempts
     *
     * @return array
     */
    public function getAttempts()
    {
        return $this->attempts;
    }
    public function addAttempt(AttemptResource $attempt)
    {
      $this->attempts[] = $attempt;
      return $this;
    }

    public function removeAttempt(AttemptResource $attempt)
    {
      $this->attempts->removeElement($attempt);
    }

    /**
     * Return the item serialization class corresponding to the type
     *
     * @param string $type
     *
     * @return string
     * @throws \LogicException
     */
    static public function getClass($type)
    {
        switch ($type) {
            case CommonExercise::MULTIPLE_CHOICE:
                $class = self::MULTIPLE_CHOICE_CLASS;
                break;
            case CommonExercise::GROUP_ITEMS:
                $class = self::GROUP_ITEMS_CLASS;
                break;
            case CommonExercise::ORDER_ITEMS:
                $class = self::ORDER_ITEMS_CLASS;
                break;
            case CommonExercise::PAIR_ITEMS:
                $class = self::PAIR_ITEMS_CLASS;
                break;
            case CommonExercise::OPEN_ENDED_QUESTION:
                $class = self::OPEN_ENDED_QUESTION_CLASS;
                break;
            case CommonExercise::TEXT_WITH_HOLES:
                $class = self::TEXT_WITH_HOLES_CLASS;
                break;
            default:
                throw new \LogicException('Unknown type');
        }

        return $class;
    }
}
