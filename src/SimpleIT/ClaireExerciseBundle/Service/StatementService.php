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

class StatementService
{
    // Constantes de propriétés xAPI --------------------------------------------------------------
    const ASKER_PROFILE_HOME                = "https://asker.univ-lyon1.fr/";
    const EXTENSION_FRAMEWORK_ID            = "https://comper.fr/xapi/frameworkId";
    const EXTENSION_NODE_ID                 = "https://comper.fr/xapi/nodeId";
    const EXTENSION_RECOMMENDATION_LOCATION = "https://comper.fr/xapi/recommendationLocation";
    const EXTENSION_RECOMMENDATION_TITLE    = "https://comper.fr/xapi/recommendationTitle";

    // Constantes de valeur xAPI ------------------------------------------------------------------
    public $VERB_ANSWERED      = [
        "id"      => "http://adlnet.gov/expapi/verbs/answered",
        "display" => [
            "en-US" => "answered",
            "fr-FR" => "a repondu"
        ]
    ];
    public $VERB_INTERACTED   = [
        "id"      =>"http://adlnet.gov/expapi/verbs/interacted/",
		"display" => [
            "en-US" => "interacted",
            "fr-FR" => "a interagi"
        ]
    ];

    const OBJECT_RECOMMENDATION_ID = "https://comper.fr/vocabulary/recommendation";

    // Constantes StatementService -----------------------------------------------------------------
    const TYPE_MULTIPLE_CHOICE     = 0;
    const TYPE_ORDER_ITEMS         = 1;
    const TYPE_OPEN_ENDED_QUESTION = 2;
    const TYPE_PAIR_ITEMS          = 3;
    const TYPE_GROUPE_ITEMS        = 4;

    /*************************************************************************************************************/
    private $id;
    private $timestamps;
    private $actor;
        private $actorObjectType;
        private $account;
            private $homePage;
            private $name;

    private $verb;
        private $verbid; // url/reponse
      //private $verbDisplay;
                private $verbDisplayTypeFR; // a repondu
                private $verbDisplayTypeEN; // a repondu



    private $object;
        private $objectId;
        private $objectType; // reponse
    //  private $objectDefinition;
            private $objectDefinitionType;
            private $objectDefinitionInteractionType;
            private $moreInfo;
            private $choices; // cas d'un choix simple ou multiple
            private $source; // le cas d'un pair ou group item
            private $target;// le cas d'un pair ou group item



            private $objectDefinitionName;
                private $objectDefinitionNameType; // qcm ou autre chose (attempt)
                private $objectDefinitionNameTypeFR;
           
       //   private $objectDefinitionDescription;
                private $objectDefinitionDescriptionType;// qcm ou autre chose (attempt)
            
            private $obejectExtensions;

    private $result;
        private $completion;
        private $sucess;
    //  private $resultExtension;
            private $question;
            private $askedanswers;
            private $answsers;

            private $response;
                private $laquestion;
                private $attendue;
                private $areponde;
                private $correcte;

       
    //  private $obejectresultExtensions;
             private $scaled; // note

    private $statement;


    /* SETTERS ************************************************************************************************* */

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    public function setActor($homePage, $name)
    {
        $this->actor = [
            "objectType" => "Agent",
            "account"    => [
                "homePage" => $homePage,
                "name"     => $name
            ]
        ];
    }

    public function setActorObjectType($actorObjectType)
    {
        $this->actorObjectType = $actorObjectType;
    }

    public function setHomePage($homePage)
    {
        $this->homePage = $homePage;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Définit le verbe du statement.
     * L'usage des constantes de la classe Statement est recommandé (i.e VERB_ANSWERED, VERB_INTERACTED, etc...).
     */
    public function setVerb($verb)
    {
        $this->verb = $verb;
    }
    
    public function setVerbid($verbid)
    {
        $this->verbid = $verbid;
    }

    public function setVerbDisplayTypeFR($verbDisplayTypeFR)
    {
        $this->verbDisplayTypeFR = $verbDisplayTypeFR;
    }

    public function setVerbDisplayTypeEN($verbDisplayTypeEN)
    {
        $this->verbDisplayTypeEN = $verbDisplayTypeEN;
    }

    public function setObjectFromRecommendationClick($recommendationTitle)
    {
        $this->object = [
            "id"         => StatementService::OBJECT_RECOMMENDATION_ID,
            "objectType" => "Activity",
            "definition" => [
                "name" => [
                    "en-EN" => "Recommendation link followed",
                    "fr-FR" => "Lien de recommandation suivi"
                ],
                "description" => [
                    "en-EN" => "The learner followed a recommendation link by clicking on it.",
                    "fr-FR" => "L'apprenant a suivi un lien de recommandation en cliquant dessus."
                ],
                "type" => "http://adlnet.gov/expapi/activities/link",
                "extensions" => [
                    StatementService::EXTENSION_RECOMMENDATION_TITLE => $recommendationTitle
                ]
            ]
        ];
    }

    public function setObjectFromResource()
    {
        if  (   ($this->objectDefinitionNameType=='multiple-choice') or
                ($this->objectDefinitionNameType=='order-items')
            )
         {


            $this->object =  array(
                'id'=> $this->objectId,
                'objectType'=>$this->objectType,
                'definition'=>array(     
                            'name'=>array(
                                'en-EN'=>$this->objectDefinitionNameType,
                                'fr-FR'=>$this->objectDefinitionNameTypeFR
                            ),
                            'description'=>array(
                                'fr-FR'=>$this->objectDefinitionDescriptionType
                            ),
                            'type'=>$this->objectDefinitionType,
                            'interactionType'=>$this->objectDefinitionInteractionType,
                            // 'moreInfo'=>'http:/localhost/data/xAPI/statements/acvitities/moreinfo/'.$this->moreInfo,
                            'choices'=>$this->choices,
                            'correctResponsesPattern'=>$this->askedanswers         
                )
            );
        }

        if ($this->objectDefinitionNameType=='open-ended-question'){
                   $this->object =  array(
                    'id'=> $this->objectId,
                    'objectType'=>$this->objectType,
                    'definition'=>array(    
                                'name'=>array(
                                    'en-EN'=>$this->objectDefinitionNameType,
                                    'fr-FR'=>$this->objectDefinitionNameTypeFR
                                ),
                                'description'=>array(
                                    'fr-FR'=>$this->objectDefinitionDescriptionType
                                ),
                                'type'=>$this->objectDefinitionType,
                                'interactionType'=>$this->objectDefinitionInteractionType,
                                // 'moreInfo'=>'http:/localhost/data/xAPI/statements/acvitities/moreinfo/'.$this->moreInfo,
                                'correctResponsesPattern'=>$this->askedanswers         
                    )
                );
        }
       if (($this->objectDefinitionNameType=='pair-items') or 
           ($this->objectDefinitionNameType=='group-items')
       ){
                   $this->object =  array(
                        'id'=> $this->objectId,
                        'objectType'=>$this->objectType,
                        'definition'=>array(    
                                
                                'name'=>array(
                                    'en-EN'=>$this->objectDefinitionNameType,
                                    'fr-FR'=>$this->objectDefinitionNameTypeFR
                                ),
                                
                                'description'=>array(
                                    'fr-FR'=>$this->objectDefinitionDescriptionType
                                ),
                               
                                'source'=>$this->source,
                                'target'=>$this->target,
                                   
                                'type'=>$this->objectDefinitionType,
                                'interactionType'=>$this->objectDefinitionInteractionType,
                                // 'moreInfo'=>'http:/localhost/data/xAPI/statements/acvitities/moreinfo/'.$this->moreInfo,
                                'correctResponsesPattern'=>$this->askedanswers         
                    )
                );
        }
    }

    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;
    }
    /*************************************************************************************************************/
   
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;
    }
    /*************************************************************************************************************/
    
    public function setObjectDefinitionType($objectDefinitionType)
    {
        $this->objectDefinitionType = $objectDefinitionType;
    }
    /*************************************************************************************************************/
    

    public function setObjectDefinitionInteractionType($objectDefinitionInteractionType)
    {
        $this->objectDefinitionInteractionType = $objectDefinitionInteractionType;
    }
    
    /*************************************************************************************************************/

    public function setChoices($choices)
    {
      
        $this->choices = $choices;
    }
    /*************************************************************************************************************/

    public function setSource($source)
    {
      
        $this->source = $source;
    }

    /*************************************************************************************************************/

    public function setTarget($target)
    {
      
        $this->target = $target;
    }
    /*************************************************************************************************************/

    public function setMoreinfo($moreinfo)
    {
      
        $this->moreInfo = $moreinfo;
    }

    /*************************************************************************************************************/


    public function setObjectDefinitionName($objectDefinitionName)
    {
        $this->objectDefinitionName = $objectDefinitionName;
    }
    /*************************************************************************************************************/
    
    public function setObjectDefinitionNameType($objectDefinitionNameType)
    {
        $this->objectDefinitionNameType = $objectDefinitionNameType;
    }
    /*************************************************************************************************************/

    public function setObjectDefinitionNameTypeFR($objectDefinitionNameTypeFR)
    {
        $this->objectDefinitionNameTypeFR = $objectDefinitionNameTypeFR;
    }
    /*************************************************************************************************************/

    
    public function setObjectDefinitionDescriptionType($objectDefinitionDescriptionType)
    {
        $this->objectDefinitionDescriptionType = $objectDefinitionDescriptionType;
    }
    public function setObejectExtensions($obejectExtensions)
    /*************************************************************************************************************/
    
    {
        $this->obejectExtensions = $obejectExtensions;
    }

    /*************************************************************************************************************/

    public function setCompletion($completion)
    {
        $this->completion = $completion;
    }
    /*************************************************************************************************************/
    
    public function setSucess($sucess)
    
    {
        $this->sucess = $sucess;
    }
    /*************************************************************************************************************/
    
    public function setQuestion($question)
    {
        $this->question = $question;
    }
    /*************************************************************************************************************/
    
    public function setAskedAnswers($askedanswers)
    
    {
        $this->askedanswers = $askedanswers;
    }
    /*************************************************************************************************************/
    public function setAnswer($answsers)
    
    {
        $this->answsers = $answsers;
    }
    /*************************************************************************************************************/
       
    public function setScaled($scaled)
    {
        $this->scaled = $scaled;
    }
    /*************************************************************************************************************/

    public function setAttendue($attendue)
    {
        $this->attendue = $attendue;
    }
    /*************************************************************************************************************/

    public function setaReponde($repondre)
    {
        $this->repondre = $repondre;
    }
    /*************************************************************************************************************/

    public function setCorrecte($correcte)
    {
        $this->correcte = $correcte;
    }

    /*************************************************************************************************************/
   
    public function setResponse()
    {
        $this->response = null;
    }

    /*************************************************************************************************************/
    
    

    /*************************************************************************************************************/

    public function setResult()
    {
        $this->result = 
                        array(

                            'completion'=>$this->completion,
                            'success'=>$this->sucess,
                            'response'=>$this->answsers,

                            'score'=>array(
                                'scaled'=>$this->scaled/100
                            ),

                            'extensions'=>array(
                                'http:/localhost/data/xAPI/statements/result/correct'=> $this->correcte 
                            )                               
                        );
    }


   
    /*************************************************************************************************************/

    public function getStatement()
    {
        $statement = [
            'actor'  => $this->actor,
            'verb'   =>  $this->verb,
            'object' =>  $this->object,
        ];
        if($this->timestamp !== null) $statement['timestamp'] = $this->timestamp;
        if($this->result    !== null) $statement['result']    = $this->result;
        return $statement;
    }

    public function setStatement()
    {
        $this->statement = 
                        
                            array(
                                    'actor'     =>  $this->actor,
                                    'verb'      =>  $this->verb,
                                    'object'    =>  $this->object,
                                    'result'    =>  $this->result,
                                    'timestamp' =>  $this->timestamp
                                    
                             );
        
    }

    


}
