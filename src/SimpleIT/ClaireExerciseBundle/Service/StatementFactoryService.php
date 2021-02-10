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
    // apprenant visé sur un intervalle  de temps
    public $user;
    public $tmin;
    public $tmax;

// paramètres de connexion  LRS

    public $url;
    public $login;
    public $password;

// paramètres de connexion  BD Asker
    public $dbhost;
    public $dblogin;
    public $dbpassword;
    public $dbdatabase;
    public $sqlResult;


// varialbes utilisés pour instancier le statement XAPI 

    public $urlStatement;

    public  $questionType;
    public  $questionTypeFR;
    public  $question;

    public  $AskedAnswers;
    public $learnerAnswer;
    public  $Answers;
    public $isCorrectAnswer;


    public  $isCorrect;

    public  $interactionType;
    public  $moreinfo;

    public  $Choices;
    public  $source;
    public  $target;
    


    public $itemContent;
    public $exrciceContent;


    public $StatementJSON;

    function __construct($comper_lrs_endpoint, $comper_lrs_creds){
        $this->urlStatement = $comper_lrs_endpoint;
        $this->endpointCreds = $comper_lrs_creds;
        $this->Choices = null; // utilisé par : statement-object- multiple-choice, order-itemContent
        $this->source = null;  // utilisé par: statement-object- pair-items, group-items 
        $this->target = null; // target et source vont ensemble dans les objets XAPI
        $this->moreinfo = "none";
        $this->AskedAnswers = null;
        $this->Answers = null;
        $this->isCorrect = null;
	    $this->question = null;

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
        // The main entry to retrieve all the informations needed to build the statement
        $item = $doctrine->getRepository('SimpleITClaireExerciseBundle:CreatedExercise\Item')->find($itemResource->getItemId());

        // The data we'll pass to the Statement we'll build.
        $data = [];

        // This will check if the answer is linked with at least one directory with a frameworkId, and if the user is linked with that directory.
        // If not, this means that the exercise done has nothing to do with the comper project and does not need to create a new xAPI statement.
        $frameworkIds = $doctrine->getRepository('SimpleITClaireExerciseBundle:Directory')->getFrameworkIdsFromUserAndModel($user->getID(), $item->getStoredExercise()->getId());
        if(count($frameworkIds) === 0) return null;  

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

        $userId = "asker:".$user->getUsername();
        $statement->setActor(StatementService::ASKER_PROFILE_HOME, $userId);

        $statement->setVerb($statement->VERB_ANSWERED);

        $statement->setObjectFromResource($data);
        $statement->setResult($data);

        $timestamp = new \DateTime();
        $timestamp = $timestamp->format(\DateTime::ISO8601);
        $statement->setTimestamp($timestamp);

        return $statement->getStatement();
    }


    /**************************************************************************************************************************************************************/
    /**************************************************************************************************************************************************************/
    // les fonctions suivantes 
        
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

        /*
            $this->questionTypeFR  = 'choix-multiple';
            $this->interactionType = 'choice'; 
            $this->isCorrectAnswer = false;

            // La question posée

            if (isset($this->itemContent->question))
                $this->question = strip_tags(html_entity_decode($this->itemContent->question));

            // Les choix posées
            
            $propositions = $this->itemContent->propositions;

            //  construire au fur et à mesure les réponses de l'apprenant

            for ($i = 0; $i < sizeof($propositions); $i++) {

                $this->isCorrectAnswer = false;
                if ($this->learnerAnswer->content[$i] == 1)
                    $this->isCorrectAnswer = true;

                $this->Choices[] = array(
                    'id' => "$i",
                    'description' => array('fr-FR' => $propositions[$i]->text)
                );

                if ($propositions[$i]->right == true)
                    $this->AskedAnswers = $this->AskedAnswers . "]" . $i . "[,";

                if ($this->learnerAnswer->content[$i] == 1) 
                    $this->Answers = $this->Answers . "]" . $i . "[,";
                
                if ($this->isCorrectAnswer == true) 
                    if  ($propositions[$i]->right == $this->isCorrectAnswer)
                        $this->isCorrect[] = true;
                    else
                        $this->isCorrect[] = false;
            }

            $this->Answers = substr($this->Answers, 1, -2);
            $this->AskedAnswers = array(substr($this->AskedAnswers, 1, -2));  
        */      
    }

/**************************************************************************************************************************************************************/
    
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

        /* 
            $this->isCorrectAnswer = false;

            $this->questionTypeFR = 'réponse-ouverte';
            $this->interactionType = 'fill-in';
            $this->question = strip_tags(html_entity_decode($this->itemContent->question));

            // if (isset($this->itemContent->comment)){
            //     $this->moreinfo = strip_tags(html_entity_decode($this->itemContent->comment));
            //     $this->moreinfo=str_replace(' ', '_', $this->moreinfo);
            // }

            $this->isCorrectAnswer = $this->learnerAnswer->content->answer;

            if (isset($this->itemContent->solutions[0])) {
                if ($this->itemContent->solutions[0] == $this->isCorrectAnswer)
                    $this->isCorrect[] = true;
                else
                    $this->isCorrect[] = false;

                $this->AskedAnswers[] = $this->itemContent->solutions[0];;
                $this->Answers = $this->isCorrectAnswer;
            }
        */
    }

/**************************************************************************************************************************************************************/

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

        /*
            $this->questionTypeFR = 'ordonnancement';
            $this->interactionType = 'sequencing';
            $this->isCorrectAnswer = false;

            $this->question = strip_tags(html_entity_decode($this->exrciceContent->wording));
            $propositions = $this->itemContent->objects;

            $Solutions = null;

            foreach ($this->itemContent->solutions as $SolutionId)
                $Solutions[] = $SolutionId;

            for ($i = 0; $i < sizeof($propositions); $i++) {

                $this->Choices[] = array(
                    'id' => "$i",
                    'description' => array('fr-FR' => strip_tags(html_entity_decode($propositions[$i]->text)))
                );

                $this->AskedAnswers = $this->AskedAnswers . "]" . $Solutions[$i + 1] . "[,";

                $this->isCorrectAnswer = $this->learnerAnswer->content[$i];
                $this->Answers = $this->Answers . $this->isCorrectAnswer;

                if ($Solutions[$i + 1] == $this->isCorrectAnswer)
                    $this->isCorrect[] = true;
                else
                    $this->isCorrect[] = false;
            }

            $this->AskedAnswers = array(substr($this->AskedAnswers, 1, -2));
        */
    }
/**************************************************************************************************************************************************************/

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

        // AJOUTER EXTENSION CORRECT (voir isCorreect et livrable)

        /*
            $temp = null;
            $askedSolution = null;

            for ($j = 0; $j < sizeof($this->itemContent->solutions[$i]); $j++) {
                $askedSolution[] = strip_tags(html_entity_decode($this->itemContent->mobile_parts[$this->itemContent->solutions[$i][$j]]->text));
                $temp = $temp . $this->itemContent->solutions[$i][$j] . ",";
            }
            $temp = substr($temp, 0, -1);

            $this->AskedAnswers = $this->AskedAnswers . "]" . $i . "[.]" . $temp . "[,";
            $temp = null;

            $this->Answers = $this->Answers . "]" . $i . "[.]" . $this->learnerAnswer->content[$i] . "[,";
            $this->isCorrectAnswer = strip_tags(html_entity_decode($this->itemContent->mobile_parts[$this->learnerAnswer->content[$i]]->text));

            if (in_array($this->isCorrectAnswer, $askedSolution))
                $this->isCorrect[] = true;
            else
                $this->isCorrect[] = false;

            $this->AskedAnswers = array(substr($this->AskedAnswers, 1, -2));
            $this->Answers      = substr($this->Answers, 1, -2);
        */
    }

/**************************************************************************************************************************************************************/

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

        /*


            $this->interactionType = 'matching';
            $this->questionTypeFR  = 'regroupement';


            $this->question = strip_tags(html_entity_decode($this->exrciceContent->wording));

            // if (isset($this->itemContent->comment)){
            //     $this->moreinfo = strip_tags(html_entity_decode($this->itemContent->comment));
            //     $this->moreinfo=str_replace(' ', '_', $this->moreinfo);
            // }



            $groups = $this->itemContent->groups;
            $propositions = $this->itemContent->objects;
            $solutions = $this->itemContent->solutions;

            for ($i = 0; $i < sizeof($groups); $i++) {
                $this->source[] = array(
                    'id' => "$i",
                    'description' => array('fr-FR' => $groups[$i])
                );
            }

            for ($i = 0; $i < sizeof($propositions); $i++) {

                if (isset($propositions[$i]->text))
                    $description = strip_tags(html_entity_decode($propositions[$i]->text));
                else
                    $description = 'image: ' . $propositions[$i]->source;

                $this->target[] = array(
                    'id' => "$i",
                    'description' => array('fr-FR' => $description)
                );
            }

            for ($i = 0; $i < sizeof($groups); $i++) {
                $temp = null;
                for ($j = 0; $j < sizeof($solutions); $j++) {
                    if ($solutions[$j] == $i)
                        $temp = $temp . ',' . $j;
                }

                $temp = substr($temp, 1);

                $this->AskedAnswers = $this->AskedAnswers . "]" . $i . "[.]" . $temp . "[,";
            }

            for ($i = 0; $i < sizeof($groups); $i++) {
                $temp = null;
                for ($j = 0; $j < sizeof($this->learnerAnswer->content->obj); $j++) {
                    if ($this->learnerAnswer->content->obj[$j] == $i)
                        $temp = $temp . ',' . $j;
                }

                $temp = substr($temp, 1);

                $this->Answers = $this->Answers . "]" . $i . "[.]" . $temp . "[,";
            }

            for ($i = 0; $i < sizeof($propositions); $i++) {
                if ($groups[$solutions[$i]] == $groups[$this->learnerAnswer->content->obj[$i]])
                    $this->isCorrect[] = true;
                else
                    $this->isCorrect[] = false;
            }

            $this->AskedAnswers = array(substr($this->AskedAnswers, 1, -2));
            $this->Answers = substr($this->Answers, 1, -2);

        */

    }
/**************************************************************************************************************************************************************/
/**************************************************************************************************************************************************************/

    public function GetStatements()
    {

        set_time_limit(3600); // 1 jeure de calcul estimée pour créer les statements 

        // se connecter à la  BD asker et récupérer les valeurs pour instancier les statements

        $sql = new mysqlRequests;
        $sql->initConnexion(

                        $this->dbhost,
                        $this->dblogin,
                        $this->dbpassword,
                        $this->dbdatabase

        );
        
        $sql->getUserAnswers($this->user, $this->tmin,  $this->tmax);        
        $this->sqlResult = $sql->getResult();

        // créer pour chaque lignée récupérée un statement XAPI

        foreach ($this->sqlResult as $line) {

            $this->questionType = $line->questionType;
            $trace = new Statement();

            //******************************************************************************************************** */
            // créer l' "Agent"

            $trace->setActorObjectType('Agent');
            $trace->setName('user'.$this->user);
            $trace->setMbox("mailto:asker@asker.com");            

            // créer le "verb"
            $trace->setVerbid('http://adlnet.gov/expapi/verbs/answered');
            $trace->setVerbDisplayTypeFR('a repondu');
            $trace->setVerbDisplayTypeEN('answered');

            // créer l' "object"
            $trace->setObjectId($this->urlStatement . '/activities/ASKER:' . $line->modelId);
            $trace->setObjectType('Activity');
            $trace->setObjectDefinitionType('http://adlnet.gov/expapi/activities/cmi.interaction');
            $trace->setObjectDefinitionNameType($this->questionType);

            // créer l'"answer" 
            $trace->setTimestamps($line->createdAt);
            $this->learnerAnswer = json_decode($line->ansewerContent);
            $this->itemContent = json_decode($line->itemContent); 
            $this->exrciceContent = json_decode($line->exerciseContent); 

            //********************************************************************************************************

            // créer les questions posées, les réponses attendus pas l'enseignant et les réponses de l'apprenant par type de question

            if ($this->questionType == 'multiple-choice') {
                $this->MultipleChoice($this->learnerAnswer,   $this->itemContent);
                $trace->setChoices($this->Choices);
            }


            /**************************************************************************************************************************************************************/

            if ($this->questionType == 'open-ended-question')
                $this->OpenEndedQuestion( $this->learnerAnswer,   $this->itemContent);
            
            /**************************************************************************************************************************************************************/
            if ($this->questionType == 'order-items') {
                $this->OrderItems($this->learnerAnswer,  $this->itemContent,  $this->exrciceContent);
                $trace->setChoices($this->Choices);
            }

            /**************************************************************************************************************************************************************/


            if ($this->questionType == 'pair-items') {
                $this->PairItems($this->learnerAnswer, $this->itemContent,  $this->exrciceContent);
                $trace->setSource($this->source);
                $trace->setTarget($this->target);
            }


            /**************************************************************************************************************************************************************/

            if ($this->questionType == 'group-items') {
                $this->GroupItems($this->learnerAnswer,   $this->itemContent,  $this->exrciceContent);
                $trace->setSource($this->source);
                $trace->setTarget($this->target);
            }

            /**************************************************************************************************************************************************************/
           
            $trace->setCompletion(true);

            if ($line->mark == 100)
                $trace->setSucess(true);
            else
                $trace->setSucess(false);

            $trace->setObjectDefinitionDescriptionType($this->question);
            $trace->setObjectDefinitionInteractionType($this->interactionType);

            $trace->setObjectDefinitionNameTypeFR($this->questionTypeFR);
            $trace->setQuestion($this->question);
            //$trace->setMoreinfo($this->moreinfo);
            $trace->setAskedAnswers($this->AskedAnswers);
            $trace->setAnswer($this->Answers);
            $trace->setCorrecte($this->isCorrect);

            $trace->setScaled($line->mark);

            /**************************************************************************************************************************************************************/
            /**************************************************************************************************************************************************************/

            // créer le statement

            $trace->setActor();
            $trace->setVerb();
            $trace->setObject();
            $trace->setResult();
            $trace->setStatement();
            $Statement[] = $trace->getStatement();

            /**************************************************************************************************************************************************************/

            $this->AskedAnswers = null;
            $this->Choices = null;
            $this->source = null;
            $this->target = null;
            $this->Answers = null;
            $this->isCorrect = null;
            $this->question = null;
            unset($trace);

            // envoyer les statements 1 par 1
            // $this->StatementJSON
            // $this->sendStatements($trace->getStatement());

            
        }

        /**************************************************************************************************************************************************************/
        /**************************************************************************************************************************************************************/

        $this->sqlResult = null;
        $this->StatementJSON = json_encode($Statement,  JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        //var_dump($this->StatementJSON);
        $Statement = null;

        // Send all statements!!

        // var_dump($this->sendStatements());
    }
}
