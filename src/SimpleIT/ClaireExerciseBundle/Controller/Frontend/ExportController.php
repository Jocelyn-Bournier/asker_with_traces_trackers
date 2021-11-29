<?php
/*
  This file is part of CLAIRE.
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

namespace SimpleIT\ClaireExerciseBundle\Controller\Frontend;

use SimpleIT\ClaireExerciseBundle\Controller\BaseController;
use SimpleIT\ClaireExerciseBundle\Entity\Directory;

//use Symfony\Component\Security\Core\SecurityContext;

use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResourceFactory;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class ExportController
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ExportController extends BaseController
{
    public function exportAction()
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            $dirs = $this->get('simple_it.exercise.directory')->allParents();
        }else if ($this->get('security.authorization_checker')->isGranted('ROLE_WS_CREATOR')){
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $dirs = $this->get('simple_it.exercise.directory')->allParents($user);
        }
        $teachers = $this->get('simple_it.exercise.user')->allTeachers();
        foreach($dirs as $key => $dir){
            $dirs[$key]['currentStudents'] = $this
                ->get('simple_it.exercise.directory')
                ->countCurrentStudents($dir['id'], array_column($teachers,"id"))[0]['total'];
            $dirs[$key]['oldStudents'] = $this
                ->get('simple_it.exercise.directory')
                ->countOldStudents($dir['id'], array_column($teachers,"id"))[0]['total'];

        }
        return $this->render(
            'SimpleITClaireExerciseBundle:Frontend:list_export.html.twig', array(
                'dirs' => $dirs,
            )
        );
    }


    public function filterDirectoryAction(Directory $directory,  $view = null)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if ($directory->hasUser($user)
            || $this->get('security.authorization_checker')->isGranted('ROLE_WS_CREATOR')
        ){
            $users = $this->get('simple_it.exercise.directory')->getIdUsers($directory, $view);
            $this->get('simple_it.exercise.directory')->hasView($directory);
            if ($view == null){
                $view = $directory->getLastView();
            }
            $params = array(
                'directory' => $directory,
                'selectView' => $view,
                'users' => $users,
            );
            if ($view){
            }
            if (!is_null($view)){
                // every users connected between frame time
                $directories = $this->get('simple_it.exercise.directory')->getModelStats($directory,$view, $users);
                $params['directories'] = $directories;
            }
            return $this->render(
                'SimpleITClaireExerciseBundle:Frontend:detail_export_directory.html.twig',$params
            );
        }
        return $this->redirectToRoute('admin_export');
    }




    // converts the json Asker sends to AMC LaTeX and returns that
    function convert_question($raw_json){
        $data = json_decode($raw_json, true);
        $nb_right_question = count(array_filter($data['propositions'], function ($prop){ return $prop['right']; }));

        $env = ($nb_right_question > 1) ? "questionmult" : "question";
        $result  = "\begin{".$env."}{".hash("md5", $raw_json)."}\n";
        $result .= $data["question"]."\n";
        $result .= "\begin{choices}\n";
        foreach($data["propositions"] as $prop){
            $truthness = ($prop["right"]) ? "correct" : "wrong";
            $result .= "\\".$truthness."choice{".$prop["text"]."}\n";
        }
        $result .= "\\end{choices}\n";
        $result .= "\\end{".$env."}\n";
        return $result;
    }

    public function createQcmAction($model)
    {
        try {
            $header = "\\documentclass[a4paper]{article}

\\usepackage[utf8x]{inputenc}
\\usepackage[T1]{fontenc}

\\usepackage[box,completemulti]{automultiplechoice}
\\begin{document}

\\onecopy{10}{

%%% beginning of the test sheet header:

\\noindent{\\bf QCM  \\hfill TEST}

\\vspace*{.5cm}
\\begin{minipage}{.4\\linewidth}
\\centering\large\bf Test\\\\ Examination on \\today\\end{minipage}
\\namefield{\\fbox{
                \\begin{minipage}{.5\\linewidth}
                  Nom et Prénom

                  \\vspace*{.5cm}\\dotfill
                  \\vspace*{1mm}
                \\end{minipage}
         }}

\\begin{center}\\em
Duration : 10 minutes.
  Les question possédant le symbole \\multiSymbole{} peuvent avoir
  aucune, une seule ou plusieurs réponses correctes. Les autres questions n'ont
  qu'une seule réponse correcte.

" . $wording . "

\\end{center}
\\vspace{1ex}

%%% end of the header

";

            $footer = "}
\\end{document}";

            $nb_of_exercises = (int) $this->get('request')->request->get('numero');

            $exercises = "";
            for($i = 0; $i < $nb_of_exercises; $i++){
                // creates an exercise
                $exercise = $this->get('simple_it.exercise.stored_exercise')->addByExerciseModel(
                    $model
                );
                $exerciseResource = ExerciseResourceFactory::create($exercise);
                $em = $this->getDoctrine()->getManager();

                // gets the newly created exercise's id
                $dql = 'SELECT s.id, s.content FROM SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\StoredExercise s WHERE s.id = :id';
                $query = $em->createQuery($dql)->setParameter('id', $exerciseResource->getId());
                $exo = $query->execute();

                $id = $exo[0]['id'];
                $wording = json_decode($exo[0]['content'])->wording;
                $item_count = json_decode($exo[0]['content'])->item_count;

                // gets the questions from the exercise queried above
                $dql = 'SELECT i.content FROM SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Item i WHERE i.storedExercise = :id';
                $query = $em->createQuery($dql)->setParameter('id', $id);
                $exo = $query->execute();

                $questions = array_map(function($question) { return $question['content']; }, $exo);

                $questions = join(
                    array_map(
                        function ($question) { return $this->convert_question($question); },
                        $questions),
                    "\n");
                $exercises .= $questions . "\n";
            }

            $find = array("_", "{", "}"); // all characters to be escaped
            $escape_chars = function($x) { return "\\" . $x;};
            $replace = array_map($escape_chars, $find);

            $content = $header . str_replace($find, $replace, $exercises) . $footer;

            $dql = 'SELECT m.title FROM SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel m WHERE m.id = :id';
            $query = $em->createQuery($dql)->setParameter('id', $model);
            $title = $query->execute();

            // make a fake file with the content in it to make the client download it
            $response = new Response();
            $response->headers->set('Content-Type', 'mime/type');
            $response->headers->set('Content-Disposition', 'attachment;filename="'.$title[0]['title'].".tex");

            $response->setContent($content);
            return $response;

            /* return new ApiGotResponse($number, array("exercise", 'Default')); */
        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException('Exercise Model');
        }
    }
}
?>
