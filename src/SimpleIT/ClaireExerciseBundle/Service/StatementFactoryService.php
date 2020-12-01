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

/**************************************************************************************************************************************************************/
    function __construct($comper_lrs_endpoint, $comper_lrs_creds){


        $this->urlStatement = $comper_lrs_endpoint; // mettre la bonne url une fois le serveur XAPI installé
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

/**************************************************************************************************************************************************************/

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
        $test = [
            "response" => $response,
            "statement" => json_decode($statements)
        ];

        return $test;
    }

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


/**************************************************************************************************************************************************************/
/**************************************************************************************************************************************************************/
// les fonctions suivantes 
    
public function MultipleChoice(){

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
}


/**************************************************************************************************************************************************************/
    
    public function OpenEndedQuestion(){
        
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

    }

/**************************************************************************************************************************************************************/

    public function OrderItems() {

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

    }
/**************************************************************************************************************************************************************/

    public function PairItems() {

        $this->questionTypeFR = 'appariement';
        $this->interactionType = 'matching';
        $this->isCorrectAnswer = false;

        $this->question = strip_tags(html_entity_decode($this->exrciceContent->wording));

        for ($i = 0; $i < sizeof($this->itemContent->fix_parts); $i++) {

            if ($this->itemContent->fix_parts[$i]->object_type == 'picture')
                $description = 'image: ' . $this->itemContent->fix_parts[$i]->source;
            else
                $description = strip_tags(html_entity_decode($this->itemContent->fix_parts[$i]->text));

            $this->source[] = array(
                'id' => "$i",
                'description' => array('fr-FR' => $description)
            );

            $this->target[] = array(
                'id' => "$i",
                'description' => array('fr-FR' => $this->itemContent->mobile_parts[$i]->text)
            );


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
        }

        $this->AskedAnswers = array(substr($this->AskedAnswers, 1, -2));
        $this->Answers = substr($this->Answers, 1, -2);

    }

/**************************************************************************************************************************************************************/

    public function GroupItems() {

        $this->interactionType = 'matching';
        $this->questionTypeFR = 'regroupement';


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
