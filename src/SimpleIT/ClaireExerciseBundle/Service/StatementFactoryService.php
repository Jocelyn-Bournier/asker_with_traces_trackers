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

namespace SimpleIT\ClaireExerciseBundle\Service;


class StatementFactoryService
{
    private $urlStatement;
    private $endpointCreds;

    function __construct($comper_lrs_endpoint, $comper_lrs_creds){
        $this->urlStatement = $comper_lrs_endpoint;
        $this->endpointCreds = $comper_lrs_creds;
    }

    /**
     * Send a statement to the lrs.
     */
    public function sendStatements($statements)
    {
        $statements = json_encode($statements);

        $header = array();
        $header[] = 'X-Experience-API-Version: 1.0.0';
        $header[] = 'Content-Type: application/json';
        $header[] = 'Response-Type: application/json';
        $header[] = 'Comper-origin: asker';
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->urlStatement);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($curl, CURLOPT_USERPWD, $this->endpointCreds);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $statements);

        $response = curl_exec($curl);
        return $response;
    }

    /**
     * Deprecated. No longer in use.
     * Generated a statement of an event on recommendation click by the learner. 
     * Now we simply record this event in the asker database.
     */
    public function generateRecommendationClickStatement($user, $recommendationTitle)
    {
        $statement = new StatementService();

        $userId = "asker:".$user->getUsername();
        $statement->setActor(StatementService::ASKER_PROFILE_HOME, $userId);

        $statement->setVerb($statement->VERB_INTERACTED);

        $statement->setObjectFromRecommendationClick($recommendationTitle);

        $timestamp = new \DateTime();
        $timestamp = $timestamp->format(\DateTime::ISO8601);
        $statement->setTimestamp($timestamp);

        return $statement->getStatement();
    }

    /**
     * Generate a statement for an exercise done by the learner.
     * 
     * $user is the learner
     * $itemResource, $attemptId and $answerResource are all entry points to help us retrieve the data we need to create the statement.
     * $doctrine let us use some repositories.
     */
    public function generateAnswerStatement($user, $itemResource, $attemptId, $answerResource, $doctrine)
    {
        echo "generation of statement";
        // The main entry to retrieve all the informations needed to build the statement
        $item = $doctrine->getRepository('SimpleITClaireExerciseBundle:CreatedExercise\Item')->find($itemResource->getItemId());

        // The data we'll pass to the Statement we'll build.
        $data = [];

        // This will check if the answer is linked with at least one directory with a frameworkId, and if the user is linked with that directory.
        // If not, this means that the exercise done has nothing to do with the comper project and does not need to create a new xAPI statement.
        echo $user->getID();
        echo $item->getStoredExercise()->getId();
        $frameworkIds = $doctrine->getRepository('SimpleITClaireExerciseBundle:Directory')->getFrameworkIdsFromUserAndModel($user->getID(), $item->getStoredExercise()->getId());

        // COMMENT FOR DEBUG PURPOSE
        echo count($frameworkIds);
        if(count($frameworkIds) === 0) return null;
        echo "going to create trace";

        // TODO : Pour chaque retrieve<type> fonction, ajouter le calcu de l'extension "correct" (voir livrable)
        //        Cette extension va permettre de définir pour chaque réponse d'un ensemble de réponse (QCM, Appariement) quelle réponse atomique est juste ou fausse.
        //        On peut récupérer cette info en comparant la réponse de l'étudiant aux responses correctes possibles.
        // If we deal with a mutliple-choice model, we need to retrieve the choices and the correct pattern.
        if($itemResource->getType() === 'multiple-choice'){
            $itemContent          = json_decode($item->getContent(), true);
            $exerciseModelContent = json_decode($item->getStoredExercise()->getExerciseModel()->getContent(), true);
            $this->retrieveMultipleChoiceData($data, $itemContent, $exerciseModelContent, $answerResource);
        }
        else if($itemResource->getType() === 'order-items'){
            $itemContent          = json_decode($item->getContent(), true);
            $exerciseModelContent = json_decode($item->getStoredExercise()->getExerciseModel()->getContent(), true);
            $this->retrieveOrderItemsData($data, $itemContent, $exerciseModelContent, $answerResource);
        }
        else if($itemResource->getType() === 'pair-items'){
            $itemContent          = json_decode($item->getContent(), true);
            $exerciseModelContent = json_decode($item->getStoredExercise()->getExerciseModel()->getContent(), true);
            $this->retrievePairItemsData($data, $itemContent, $exerciseModelContent, $answerResource);
        }
        else if($itemResource->getType() === 'group-items'){
            $itemContent          = json_decode($item->getContent(), true);
            $exerciseModelContent = json_decode($item->getStoredExercise()->getExerciseModel()->getContent(), true);
            $this->retrieveGroupItemsData($data, $itemContent, $exerciseModelContent, $answerResource);
        }
        else if($itemResource->getType() === 'open-ended-question'){
            $itemContent          = json_decode($item->getContent(), true);
            $exerciseModelContent = json_decode($item->getStoredExercise()->getExerciseModel()->getContent(), true);
            $this->retrieveOpenEndedQuestionData($data, $itemContent, $exerciseModelContent, $answerResource);
        }
        

        $attempt = $doctrine->getRepository('SimpleITClaireExerciseBundle:CreatedExercise\Attempt')->find($attemptId);
        $answer  = $doctrine->getRepository('SimpleITClaireExerciseBundle:CreatedExercise\Answer')->findAllBy(null, $attempt);

        $data['modelId']     = $item->getStoredExercise()->getExerciseModel()->getId();
        $data['type']        = $itemResource->getType();
        $data['mark']        = $answer[count($answer) - 1]->getMark();
        $statement = new StatementService();

        $userId = "asker:".$user->getId();
        $statement->setActor(StatementService::ASKER_PROFILE_HOME, $userId);

        $statement->setVerb($statement->VERB_ANSWERED);

        $statement->setObjectFromResource($data);
        $statement->setResult($data);

        $timestamp = new \DateTime();
        $timestamp = $timestamp->format(\DateTime::ISO8601);
        $statement->setTimestamp($timestamp);

        return $statement->getStatement();
    }
        
    public function retrieveMultipleChoiceData(&$data, $itemContent, $exerciseModelContent, $answerResource){

        // Create the question description, i.e the model exercise statement + the stored exercise spécific condition.
        $question = $itemContent['question'];
        $question = $exerciseModelContent['wording'].' '.$question;
        $data['description'] = $question;

        $rawChoices    = $itemContent['propositions'];
        $choices       = [];
        $correctAnswer = [];
        for($i = 0; $i < count($rawChoices); $i++){
            $choices[] = [
                'id' => strval($i),
                'description' => [
                    'fr-FR' => $rawChoices[$i]['text']
                ]];
            if($rawChoices[$i]['right']) $correctAnswer[] = $i;
        }
        $correctAnswer = implode('[,]', $correctAnswer);


        $answers       = [];
        $rawAnswers    = $answerResource->getContent();
        for($i = 0; $i < count($rawAnswers); $i++){
            if($rawAnswers[$i] === 1) $answers[] = $i;
        }
        $answers       = implode('[,]', $answers);
        $data['answers']     = $answers;

        // We set the data with those informations.
        $data['choices']       = $choices;
        $data['correctAnswer'] = [$correctAnswer];    
    }
    
    public function retrieveOpenEndedQuestionData(&$data, $itemContent, $exerciseModelContent, $answerResource){
        
        // Create the question description, i.e the model exercise statement + the stored exercise spécific condition.
        $question = $itemContent['question'];
        $data['description'] = strip_tags(html_entity_decode($question));

        $rawChoices    = $itemContent['solutions'];
        if(isset($rawChoices[0])){
            $data['correctAnswer'] = [$rawChoices[0]];
        }
        else{
            $data['correctAnswer'] = [""];
        }

        $answers         = [];
        $rawAnswers      = $answerResource->getContent();
        $data['answers'] = $rawAnswers['answer'];
    }

    public function retrieveOrderItemsData(&$data, $itemContent, $exerciseModelContent, $answerResource) {

        // Create the question description, i.e the model exercise statement + the stored exercise spécific condition.
        $question = $exerciseModelContent['wording'];
        $data['description'] = $question;

        $rawChoices    = $itemContent['objects'];
        $choices       = [];
        for($i = 0; $i < count($rawChoices); $i++){
            $choices[] = [
                'id' => strval($i),
                'description' => [
                    'fr-FR' => strip_tags(html_entity_decode($rawChoices[$i]['text']))
                ]
            ];
        }

        $solutions     = $itemContent['solutions'];
        array_shift($solutions);
        $correctAnswer = implode('[,]', $solutions);

        $answers       = [];
        $rawAnswers    = $answerResource->getContent();
        $answers       = implode('[,]', $rawAnswers);
        $data['answers']     = $answers;

        // We set the data with those informations.
        $data['choices']       = $choices;
        $data['correctAnswer'] = [$correctAnswer];
    }

    public function retrievePairItemsData(&$data, $itemContent, $exerciseModelContent, $answerResource) {

        // Create the question description, i.e the model exercise statement.
        $question            = strip_tags(html_entity_decode($exerciseModelContent['wording']));
        $data['description'] = $question;

        $data['source']        = [];
        $data['target']        = [];
        for ($i = 0; $i < count($itemContent['fix_parts']); $i++) {
            $fixPart    = $itemContent['fix_parts'][$i];
            $mobilePart = $itemContent['mobile_parts'][$i];

            if ($fixPart['object_type'] == 'picture') $description = 'image: ' . $fixPart['source'];
            else                                      $description = strip_tags(html_entity_decode($fixPart['text']));

            $data['source'][] = [
                'id'          => strval($i),
                'description' => [
                    'fr-FR' => $description
                ]
            ];

            $data['target'][] = [
                'id'          => strval($i),
                'description' => [
                    'fr-FR' => $mobilePart['text']
                ]
            ];
        }

        // Format the Asker solutions for xAPI cmi matching correctResponsesPattern.
        $data['correctAnswer'] = [];
        $solutions             = $itemContent['solutions'];
        $correctPairs          = [];

        /** Recursive function that construct all the matching solutions.
         * An exemple: We transform 
         *   solutions = [                 
         *      [3],                 <- item source 0 can be match with item target 3
         *      [0,1,2],             <- item source 1 can be match with item target 0, 1 or 2
         *      [0,1,2],             <- -- same --
         *      [0,1,2]              <- -- same --
         *   ]
         * Into
         *   correctPairs = [
         *     [3, 0, 1, 2], 
         *     [3, 0, 2, 1],
         *     [3, 1, 0, 2],
         *     [3, 1, 2, 0],
         *     [3, 2, 1, 0],
         *     [3, 2, 0, 1]
         *   ]
         */
        function _generatePair($solutions){
          if(count($solutions) == 1){
            return array_map(function($a){return [$a];}, $solutions[0]);
          }
          $solution = array_shift($solutions);
          $pairs = [];
          
          $childSolutions = _generatePair($solutions);
          for($i = 0 ; $i < count($solution); $i++){
            for($j = 0; $j < count($childSolutions); $j++){
              if(!in_array($solution[$i], $childSolutions[$j])){
                $newPair = $childSolutions[$j];
                array_unshift($newPair, $solution[$i]);
                $pairs[] = $newPair;
              }
            }
          }
          return $pairs;
        }
        $correctPairs = _generatePair($solutions);
        foreach($correctPairs as $correctPair){
            for($i = 0; $i < count($correctPair); $i++){
                $correctPair[$i] = $i.'[.]'.$correctPair[$i];
            }
            $data['correctAnswer'][] = implode('[,]', $correctPair);
        }

        $rawAnswer = $answerResource->getContent();
        for($i = 0; $i < count($rawAnswer); $i++){
            $rawAnswer[$i] = $i.'[.]'.$rawAnswer[$i];
        }
        $data['answers'] = implode('[,]', $rawAnswer);
    }

    public function retrieveGroupItemsData(&$data, $itemContent, $exerciseModelContent, $answerResource) {

        // Create the question description, i.e the model exercise statement.
        $question            = strip_tags(html_entity_decode($exerciseModelContent['wording']));
        $data['description'] = $question;

        $data['source'] = [];
        for ($i = 0; $i < count($itemContent['groups']); $i++) {
            $groups = $itemContent['groups'][$i];
            $data['source'][] = [
                    'id'          => strval($i),
                    'description' => [
                        'fr-FR' => $groups
                ]
            ];
        }

        $data['target'] = [];
        for ($i = 0; $i < count($itemContent['objects']); $i++) {
            $object = $itemContent['objects'][$i];
            if (isset($object['text'])) $description = strip_tags(html_entity_decode($object['text']));
            else                        $description = 'image: ' . $object['source'];

            $data['target'][] = [
                    'id'          => strval($i),
                    'description' => [
                        'fr-FR' => $description
                ]
            ];
        }

        // Format the Asker solutions for xAPI cmi matching correctResponsesPattern.
        $data['correctAnswer'] = [];
        $solutions             = $itemContent['solutions'];
        for($i = 0; $i < count($solutions); $i++){
            $solutions[$i] = $i.'[.]'.$solutions[$i];
        }
        $data['correctAnswer'][] = implode('[,]', $solutions);

        $rawAnswer = $answerResource->getContent()['obj'];
        for($i = 0; $i < count($rawAnswer); $i++){
            $rawAnswer[$i] = $i.'[.]'.$rawAnswer[$i];
        }
        $data['answers'] = implode('[,]', $rawAnswer);
    }
}
