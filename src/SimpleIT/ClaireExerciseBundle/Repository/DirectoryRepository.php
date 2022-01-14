<?php

namespace SimpleIT\ClaireExerciseBundle\Repository;

use SimpleIT\ClaireExerciseBundle\Entity\Test\TestModel;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;

/**
 * DirectoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DirectoryRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAttempts($user,$model)
    {
        return $this->getEntityManager()->createQuery(
            "SELECT em.id FROM
            SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Attempt a
            JOIN a.exercise ese
            JOIN ese.exerciseModel em
            WHERE a.user  = $user
            AND em.id = $model"
        )
        ->getResult();
    }

    /**
     * Find a directory by its id
     *
     * @param mixed $directoryId
     *
     * @return Directory
     * @throws NonExistingObjectException
     */
    public function find($directoryId, $lockMode = null, $lockVersion = null)
    {
        $resource = parent::find($directoryId);
        if ($resource === null) {
            throw new NonExistingObjectException();
        }

        return $resource;
    }

    public function findNews($user)
    {
        $attemptedModels = $this->getEntityManager()->createQuery(
            "SELECT em.id FROM
            SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Attempt a
            JOIN a.exercise ese
            JOIN ese.exerciseModel em
            WHERE a.user  = $user"
        )
        ->getResult();
        $qb = $this->createQueryBuilder('d')
            ->select('
                d.id as dir,
                d.name,
                u.id as user,
                m.complete as modelComplete,
                m.type as modelType,
                m.title as modelTitle,
                m.id as modelId,
                o.firstName as firstName,
                o.lastName as lastName

            ')
            ->join('d.models', 'm')
            ->join('m.owner', 'o')
            ->join('d.users', 'u')
        ;
        if (empty($attemptedModels)){
            $qb
                ->where(
                    $qb->expr()->eq('u.id', ':user')
                    )
                ->setParameter('user', $user)
            ;
        }else{
            $qb
                ->where(
                    $qb->expr()->andX(
                        $qb->expr()->eq('u.id', ':user'),
                        $qb->expr()->notIn('m.id', ':attemptedModels')
                    )
                )
                ->setParameter('user', $user)
                ->setParameter('attemptedModels', $attemptedModels)
            ;
        }
        return $qb
            ->getQuery()
            ->getResult()
        ;
    }
    public function findBymodel($model)
    {
        return $this->createQueryBuilder('d')
            ->select('d.id, d.name')
            ->join('d.models', 'm')
            ->where('m.id = :model')
            ->setParameter('model', $model)
            ->getQuery()
            ->getArrayResult()
        ;
    }

    public function findAllApi($user)
    {
        $sql = "
            SELECT d.id, d.name, p.name as parent_name
            FROM directory d
            LEFT JOIN directory p
            ON p.id = d.parent_id
            WHERE d.isVisible is true
            and (
                d.owner_id = :user or
                d.id in (SELECT directory_id FROM asker_user_directory WHERE user_id = :user and isManager =1)
            );
        ";
        /* This request doesnt work because subdirectory does not update isManager
            SELECT d.id, d.name FROM directory d JOIN asker_user_directory aud on aud.directory_id = d.id
            WHERE d.isVisible is true and aud.user_id = 2 and isManager is true;
         */
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute(array(':user'=>$user));
        return $stmt->fetchAll();
        //$qb = $this->createQueryBuilder('d')
        //    ->select('d.id, d.name')
        //    ->join('d.users','aud')
        //    ->where('d.isVisible = true');
        //$qb->expr()
        //   ->andX(
        //$qb->expr()
        //   ->andX(
        //        $qb->expr()->eq('d.owner', ':user')

        //   )
        //    ->andWhere('d.owner = 1')
        //    ->getQuery()
        //    ->getArrayResult()
        //;
    }

    public function findNativeParents(){
        $sql = "
            SELECT  d.id, d.name
            FROM directory d
            WHERE parent_id is NULL
            ORDER BY d.name
        ";
       $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
       $stmt->execute();
       return $stmt->fetchAll();
       # new version is doctine is up to date
       #return $stmt->executeQuery()->fetchAllAssociative();
    }

    public function findParents($user = 0)
    {
        $sql = "
            SELECT d.id, d.name, COUNT(dm.model_id) as models, d.code, d.frameworkId, a.username
            FROM directory d
            LEFT JOIN directories_models dm
            ON d.id = dm.directory_id
            JOIN asker_user a
            ON a.id = d.owner_id
            WHERE parent_id IS NULL";
        if ($user !== 0){
            $sql .= "
                AND (
                    d.id  IN (
                        SELECT directory_id
                        FROM asker_user_directory
                        WHERE user_id = :user and isManager = 1
                    )
                    OR owner_id = :user
                )
            ";
        }
        $sql .= " GROUP BY d.id, d.name, d.code;";
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        if ($user !== 0){
            $stmt->execute(array(':user'=>$user->getId()));
        }else{
            $stmt->execute();
        }
        $parents =  $stmt->fetchAll();

        #remove next time
        #$qb = $this->createQueryBuilder('d')
        #    ->select('d.id,d.code,o.username, d.name, count(m.id) as models')
        #    ->leftJoin('d.models', 'm')#Permits to display directory without models
        #    //pour afficher le nom du owner
        #    ->join('d.owner', 'o') # For admin's view
        #;
        #if ($user !== 0){
        #    $parents = $qb
        #        ->join('d.users', 'aud')
        #        //->join('aud.user', 'u')
        #        ->andWhere(
        #            $qb->expr()->orX(
        #                #Must be owner OR (being manager of directory)
        #                $qb->expr()->eq('d.owner', ':user'),
        #                $qb->expr()->andX(
        #                    $qb->expr()->eq('aud.isManager', 1),
        #                    $qb->expr()->eq('aud.user', ':user')
        #                )
        #            ),
        #            $qb->expr()->isNull('d.parent')
        #        )
        #        //->where('u.id = :user')
        #        //->andWhere('d.parent IS NULL')
        #        ->setParameter('user', $user)
        #    ;
        #}else{
        #    #admins get all parents directories
        #    $parents = $qb
        #        ->where('d.parent IS NULL')
        #    ;
        #}
        #$parents = $parents
        #    ->groupBy('d.name')
        #    ->addGroupBy('d.id')
        #    ->getQuery()
        #    ->getArrayResult();
        foreach($parents as $key =>  $parent){

            $parents[$key]['models'] += $this->
                countModelChildrens($parent['id'])
                [0]['total']
            ;
            $parents[$key]['users'] = $this->
                countUsers($parent['id'])
                [0]['total']
            ;
        }
        return $parents;
    }

    public function findObjects()
    {
            return $this->createQueryBuilder('d');
    }
    public function findObjectParents()
    {
        return $this->createQueryBuilder('d')
            ->where('d.parent is NULL')
        ;
    }
    /* Used by AskerUserDirectoryType
     * requets directories where the users
     * is not the owner
     */
    public function findObjectNotMine($user)
    {
        return $this->createQueryBuilder('d')
            ->where('d.parent is NULL')
            ->andWhere('d.owner <> :user')
            ->setParameter('user', $user)
        ;
    }

    public function findMine($user)
    {
        //selection des parents
        //$parents = $this->createQueryBuilder('d')
        //    ->select('d.id')
        //    ->where('d.owner = :user')
        //    ->andWhere('d.parent IS NULL')
        //    ->setParameter('user', $user)
        //    ->getQuery()
        //    ->getArrayResult();
        $qb = $this->createQueryBuilder('d')
            ->select('d.id')
            ->join('d.users','aud')
	    #->join('aud.user', 'u')
        ;
        $parents = $qb
	    ->andwhere(
                    $qb->expr()->isNull('d.parent'),
                    $qb->expr()->eq('aud.user',':user'),
		    $qb->expr()->orX(
                $qb->expr()->eq('aud.isManager',1),
	    	$qb->expr()->eq('d.owner',':owner')
		    )
	    )
            #->orWhere(
            #    $qb->expr()->eq('u.isManager',1),
            #    $qb->expr()->andX(
            #        $qb->expr()->eq('d.owner',':user'),
            #        $qb->expr()->isNull('d.parent')
            #    )
            #)
            ->setParameter('user', $user)
            ->setParameter('owner', $user)
            ->getQuery()
            ->getArrayResult()
        ;

        //recuperation des ids
        $ids = array_column($parents,"id");
        $qb =$this->createQueryBuilder('d')
            ->select('d.id, d.isVisible as is_visible, d.code, d.frameworkId, d.name, p.id as idp')
            ->leftJoin('d.users', 'aud')
            ->leftJoin('d.parent', 'p')
        ;
        #return $qb->where(
        return $qb->andWhere(
                    $qb->expr()->eq('aud.user',':user'),
                $qb->expr()->orX(
                    $qb->expr()->in('d.parent',$ids),
                    $qb->expr()->eq('d.owner',':user'),
                    $qb->expr()->eq('aud.isManager',1)
                )
            )
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function findChildrens($parent)
    {
        return $this->createQueryBuilder('d')
            ->select('d.id, d.name')
            ->where('d.parent = :parent')
            ->setParameter('parent', $parent)
            ->getQuery()
            ->getResult();
    }
    public function countChildrens($parent)
    {
        return $this->createQueryBuilder('d')
            ->select('count(d.id) as total')
            ->where('d.parent = :parent')
            ->setParameter('parent', $parent)
            ->getQuery()
            ->getResult();
    }
    public function countModels($parent)
    {
        return $this->createQueryBuilder('d')
            ->select('count(m.id) as total')
            ->join('d.models', 'm')
            ->where('d.id = :parent')
            ->setParameter('parent', $parent)
            ->getQuery()
            ->getResult();
    }

    public function countModelChildrens($parent)
    {
        return $this->createQueryBuilder('d')
            ->select('count(m.id) as total')
            ->join('d.models', 'm')
            ->where('d.parent = :parent')
            ->setParameter('parent', $parent)
            ->getQuery()
            ->getResult();
    }

    public function countUsers($id)
    {
        return $this->createQueryBuilder('d')
            ->select('count(u.id) as total')
            ->join('d.users', 'u')
            ->where('d.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }
    public function countCurrentStudents($id, $teachers)
    {
        $qb = $this->createQueryBuilder('d')
            ->select('count(u.id) as total')
            ->join('d.users', 'aud')
            ->join('aud.user', 'u')
            ->join('u.roles', 'r')
        ;
        return $qb->where(
                $qb->expr()->andX(
                    #$qb->expr()->eq('r.name', ':role'),
                    $qb->expr()->isNull('aud.endDate'),
                    $qb->expr()->eq('d.id', ':id'),
                    $qb->expr()->notIn('u.id',':teachers')
                )
            )
            ->setParameter('id', $id)
            #->setParameter('role', "ROLE_USER")
            ->setParameter('teachers', $teachers)
            ->getQuery()
            ->getResult();
    }
    public function countOldStudents($id, $teachers)
    {
        $qb = $this->createQueryBuilder('d')
            ->select('count(u.id) as total')
            ->join('d.users', 'aud')
            ->join('aud.user', 'u')
            ->join('u.roles', 'r')
        ;
        return $qb->where(
                $qb->expr()->andX(
                    #$qb->expr()->eq('r.name', ':role'),
                    $qb->expr()->isNotNull('aud.endDate'),
                    $qb->expr()->eq('d.id', ':id'),
                    $qb->expr()->notIn('u.id',':teachers')
                )
            )
            ->setParameter('id', $id)
            #->setParameter('role', "ROLE_USER")
            ->setParameter('teachers', $teachers)
            ->getQuery()
            ->getResult();
    }

    /**
     * ANR COMPER
     *
     * This function helps knowing if a learner just did an exercise in the context of COMPER (ie, in a directory with a frameworkId set)
     * Retrieves the frameworkIds of the parents directory of an exerciseModel. The learner (user) must be related to the directory.
     */
    public function getFrameworkIdsFromUserAndModel($userId, $storedExerciseId)
    {
        $sql = "
            SELECT d.frameworkId
            FROM directory d
            JOIN directories_models dm
                ON d.id = dm.directory_id
            JOIN claire_exercise_stored_exercise st
                ON st.exercise_model_id = dm.model_id
            JOIN asker_user_directory aud
                ON aud.directory_id = d.id
            WHERE st.id = :model
            AND   aud.user_id = :user
            AND   d.frameworkId IS NOT NULL
        ";
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute(
            array(
                'model' => $storedExerciseId,
                'user' => $userId
            )
        );
        return $stmt->fetchAll();
    }
    // Affichage dans le tableau d'étudiants dans les statistiques
    public function getPreviewStats($id,$user,$view)
    {
        $sql = "
            select count(a.id) as count1,
                count(an.id) as count2,
                avg(an.mark) as mark,
                min(a.created_at) as firstDate,
                max(a.created_at) as lastDate,
                min(an.created_at) as firstDate2,
                max(an.created_at) as lastDate2,
                datediff(max(a.created_at), min(a.created_at)) as days
            from claire_exercise_attempt a
            left join claire_exercise_answer an on a.id = an.attempt_id
            join claire_exercise_stored_exercise se on a.exercise_id = se.id
            join claire_exercise_model m on se.exercise_model_id = m.id
            join directories_models dm on m.id = dm.model_id
            join directory d on dm.directory_id = d.id
            where a.user_id = :user
                and (d.id = :id or d.parent_id = :id)
        ";

        $params = array(
            ':user'=> $user,
            ':id'=> $id
        );

        if($view != null){
            $sql .= "and a.created_at >= :start
                     and a.created_at <= :end";

            $params[':start'] = $view->getStartDate()->format('Y-m-d');
            $params[':end'] = $view->getEndDate()->format('Y-m-d');
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    // Affichage dans le diagramme sunburst de tous les modèles
    function JSONUserStats($model, $directory, $user, $view)
    {
        $sql = "
            SELECT AVG(an.mark) mark,
                d.name directory,
                m.id id,
                d.id mid,
                m.title name,
                COUNT(at.id) totalAtt,
                COUNT(an.id) totalAns
            FROM claire_exercise_attempt at
            LEFT JOIN claire_exercise_answer an
                ON an.attempt_id = at.id
            JOIN claire_exercise_stored_exercise st
                ON at.exercise_id = st.id
            JOIN directories_models dm
                ON st.exercise_model_id = dm.model_id
            JOIN claire_exercise_model m
                ON m.id = dm.model_id
            JOIN directory d
                ON d.id = dm.directory_id
            WHERE dm.model_id = :model
                AND d.id = :directory
                AND at.user_id = :user
        ";

        $params = array(
            'model'=>$model,
            'directory'=>$directory,
            'user'=>$user
        );

        if($view != null){
            $sql .= "and at.created_at >= :start
                     and at.created_at <= :end";

            $params[':start'] = $view->getStartDate()->format('Y-m-d');
            $params[':end'] = $view->getEndDate()->format('Y-m-d');
        }

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    function findAllModelsIds($id)
    {
        $sql = "
            SELECT m.id, d.id dir
            FROM claire_exercise_model m
            JOIN directories_models dm
                ON dm.model_id = m.id
            JOIN directory d
                ON d.id = dm.directory_id
            WHERE d.parent_id = :id
        ";
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute(array('id'=>$id));
        return $stmt->fetchAll();
    }
    // Permet de récupérer tous les modèles assiciés au dossier
    function findAllModels($id)
    {
        $sql = "
            SELECT m.id mid, d.id did
            FROM claire_exercise_model m
            JOIN directories_models dm
                ON dm.model_id = m.id
            JOIN directory d
                ON d.id = dm.directory_id
            WHERE d.parent_id = :id or d.id = :id
        ";
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute(array('id'=>$id));
        return $stmt->fetchAll();
    }
    // Permet de récupérer tous les sous-dossiers
    function getSubDirs($id)
    {
        $sql = "
            SELECT DISTINCT id
            FROM directory
            WHERE parent_id = :id or id = :id
        ";
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute(array(':id'=>$id));
        return $stmt->fetchAll();
    }
    // Permet de récupérer les informations pour le diagramme sunburst par sous-dossiers
    function getSubDirsStats($id,$user,$view)
    {
        $sql = "
            SELECT d.id id,
                d.name name,
                AVG(an.mark) mark,
                COUNT(at.id) totalAtt,
                COUNT(an.id) totalAns
            FROM claire_exercise_attempt at
            LEFT JOIN claire_exercise_answer an
                ON an.attempt_id = at.id
            JOIN claire_exercise_stored_exercise st
                ON at.exercise_id = st.id
            JOIN directories_models dm
                ON st.exercise_model_id = dm.model_id
            JOIN claire_exercise_model m
                ON m.id = dm.model_id
            JOIN directory d
                ON d.id = dm.directory_id
            WHERE at.user_id = :user
                AND (d.parent_id = :id or d.id = :id)
        ";

        $params = array(
            'id'=>$id,
            'user'=>$user
        );

        if($view != null){
            $sql .= "and at.created_at >= :start
                     and at.created_at <= :end";

            $params[':start'] = $view->getStartDate()->format('Y-m-d');
            $params[':end'] = $view->getEndDate()->format('Y-m-d');
        }
        $sql .= " GROUP BY d.id";

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    // Permet de récupérer les informations pour le diagramme sunburst par modèles dans un sous-dossier
    function getModelsStats($id,$user,$view)
    {
        $sql = "
            SELECT m.title name,
                m.id id,
                AVG(an.mark) mark,
                m.type type,
                COUNT(at.id) totalAtt,
                COUNT(an.id) totalAns
            FROM claire_exercise_answer an
            RIGHT JOIN claire_exercise_attempt at
                ON an.attempt_id = at.id
            JOIN claire_exercise_stored_exercise st
                ON at.exercise_id = st.id
            JOIN directories_models dm
                ON st.exercise_model_id = dm.model_id
            JOIN claire_exercise_model m
                ON m.id = dm.model_id
            JOIN directory d
                ON d.id = dm.directory_id
            WHERE dm.directory_id = :id
                AND at.user_id = :user
        ";

        $params = array(
            'id'=>$id,
            'user'=>$user
        );

        if($view != null){
            $sql .= "and at.created_at >= :start
                     and at.created_at <= :end";

            $params[':start'] = $view->getStartDate()->format('Y-m-d');
            $params[':end'] = $view->getEndDate()->format('Y-m-d');
        }
        $sql .= " GROUP BY m.id";

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

}
