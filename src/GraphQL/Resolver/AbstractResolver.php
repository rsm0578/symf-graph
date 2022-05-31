<?php
/**
 * Date: 31.10.16
 *
 * @author Portey Vasil <portey@gmail.com>
 */
namespace App\GraphQL\Resolver;

use App\Utility\DepthLooper;
use App\Utility\MultipleException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Query\QueryBuilder;
use FOS\UserBundle\Doctrine\UserManager;

abstract class AbstractResolver
{
    /** @var EntityManagerInterface */
    public $default_em;
    
    /** @var EntityManagerInterface */
    public $_em;
    
    /** @var EntityManagerInterface */
    public $promoted_em;
    
    /** @var UserManager */
    public $userManager;
    
    /** @var ContainerInterface */
    public $container;
    
    /** @var $args[] */
    public $args;
    
    public $entity;
    
    protected $currentUser;
    
    /** @var $info[] */
    protected $info;

    /** @var QueryBuilder */
    protected $query;

    /** @var $totalCount */
    protected $totalCount = 0;

    /** @var $targetEntity */
    protected $targetEntity;

    /** @var $fieldArrayJoinery */
    protected $fieldArrayJoinery;

    /** @var $rawFieldArrayJoinery */
    protected $rawFieldArrayJoinery;

    /** @var $fieldArray */
    protected $fieldArray;

    /** @var $whereCluaseAndParams */
    protected $whereCluaseAndParams;
    
    protected $timerStart;
    protected $timerEnd;
    
    /* TODO: THIS HAS BEEN ADDED BY KIM G */
    function __construct(EntityManagerInterface $_em, EntityManagerInterface $default_em, UserManager $userManager, ContainerInterface $container)
    {
        $this->promoted_em = $_em; // THIS WILL BE THE DEFAULT PROMOTED ENITY MANAGER UNLESS OVERRIDEN
        $this->_em = $_em;
        $this->default_em = $default_em;
        $this->userManager = $userManager;
        $this->container = $container;
        
        $tokenStorage = $this->container->get('security.token_storage')->getToken();
        if($tokenStorage != null){
            $this->currentUser = $tokenStorage->getUser();
        }
        
        $this->timerStart = microtime(true);
    }
    
    public function initAbstractResolver($args = [], $info = [])
    {
        $this->args = $args;
        $this->info = $info;
        $this->fieldArray = DepthLooper::extractNodeFields($this->info);
    }
    
    public function prepareEntity($targetClass){
        if($this->args['entity']['Id'] > 0){
            $this->entity = $this->_em->getRepository($targetClass)->findOneBy(['Id'=> $this->args['entity']['Id']]);
        }else{
            $this->entity = new $targetClass;
        }
        
        // FOREACH ENTITY TO OVERRIDE VALUES
        if($this->entity){
            foreach ($this->args['entity'] as $key => $value){
                $methodName = 'set'.$key;
                $this->entity->$methodName($value);
            }
        }
    }
    
    public function validateEntity(){
        $validator = $this->container->get('validator');
        $errors = $validator->validate($this->entity);
        if(count($errors) > 0){
            throw new \Exception(MultipleException::entityError($errors));
        }else{
            return true;
        }
    }
    
    public function mergeEntity(){
        try{
            if($this->entity->getId() > 0){
                // SET MODIFICATION INFORMATION
                $this->entity->setModifiedById($this->currentUser->getAspNetUserId());
                $this->entity->setDateModified(new \DateTime('NOW'));
                
                $this->_em->merge($this->entity);
            }else{
                // SET CREATION INFORMATION
                $this->entity->setCreatedById($this->currentUser->getAspNetUserId());
                $this->entity->setDateCreated(new \DateTime('NOW'));
                
                // SET MODIFICATION INFORMATION
                $this->entity->setModifiedById($this->currentUser->getAspNetUserId());
                $this->entity->setDateModified(new \DateTime('NOW'));
                
                $this->_em->persist($this->entity);
            }
            
            $this->_em->flush();
            $this->args['Id'] = $this->entity->getId();
            // TODO : WHEN IN MODIFY MODE Id IS NOT EXISTENT SINCE FIELDs ARE 1 LAYER DEEP in entity index of ARRAY
            return $this->args['Id'];
        }catch(\Exception $err){
            throw new \Exception($err);
            return false;
        }
    }
    
    public function prepareQueryBuilder()
    {
        // PREPARE QUERY OBJECT
        $this->query = $this->promoted_em->createQueryBuilder();
        
        // APPLY WHERE CLAUSE
        $this->queryBuilderWhereAndParameters();
        
        // SORTING
        $this->queryBuilderOrderBy();
        
        // OFFSET LIMIT / FIRST RESULT / MAX RESULT
        if (isset($this->args['paging'])) {
            $this->query->setFirstResult($this->args['paging']['offset']);
            $this->query->setMaxResults($this->args['paging']['limit']);
            
            // SET MAXIMUM LIMIT TO 500 ONLY
            if(isset($this->args['paging']['limit']) && $this->args['paging']['limit'] > 500){
                $this->query->setMaxResults(500);
            }
        } else {
            $this->query->setMaxResults(10);
        }
        
        // IF EXPORT IS PRESENT OVERRIDE MAXRESULT AND SETFIRSTRESULT
        if(isset($this->args['export']) && $this->args['export']){
            $this->query->setFirstResult(0);
            $this->query->setMaxResults(3000); // 3k MAX RECORD
        }
    }
    
    public function queryBuilderWhereAndParameters()
    {
        $parameters = [];
        // $isBrandInFilter = false;
        if (isset($this->args['filter']) && count($this->args['filter']) > 0) {
            foreach ($this->args['filter'] as $key => $value) {
                if((string) $value['parameter'] != '' && $value['expression'] != ''){
                    switch ($value['expression']) {
                        case '%@%': // CONTAINS
                            $this->query->andWhere($this->query->expr()->like($this->rawFieldArrayJoinery[$key]['joineryTerm'], ':'.$key));
                            $parameters[$key] = '%'.$value['parameter'].'%';
                            break;
                        default:
                            $this->query->andWhere($this->rawFieldArrayJoinery[$key]['joineryTerm'] . ' ' . $value['expression'] . ' :' . $key);
                            $parameters[$key] = $value['parameter'];
                    }
                }
            }
        }
        // SET ALL PARAMAETERS
        $this->query->setParameters($parameters);
    }
    
    public function queryBuilderOrderBy(){
        if (isset($this->args['sort']) && count($this->args['sort']) > 1) {
            // PROCESS COLUMN TO ORDER
            $joineryString = $this->rawFieldArrayJoinery[$this->args['sort']['field']]['joineryTerm'];
            $soryByString = (strpos($joineryString, ' AS ') !== false) ? explode(' AS ', $joineryString)[0] : $joineryString;
            $this->query->orderBy($soryByString, (($this->args['sort']['order'] == '1') ? 'ASC' : 'DESC'));
        }
    }
    
    public function getQueryTotalCount($targetEntity)
    {
        if(isset($this->args['export']) && $this->args['export']){
            $this->totalCount = 0;
            return $this->totalCount;
        }
        
        // DECPRACTED
        // TODO: MAKE THE COLUMN SUBJECT TO COUNT DYNAMIC AS WELL
        if (isset($this->args['paging'])) {
            $this->totalCount = $this->promoted_em->createQueryBuilder()
                ->select('count(a.Id)')
                ->from($targetEntity, 'a')
                ->getQuery()
                ->getSingleScalarResult();
        }
        return $this->totalCount;
    }
    
    public function preparePagingResult($queryResult, $count)
    {
        $this->timerEnd = microtime(true);
        
        $returnResult = [
            'pagingInfo' => [
                'offset' => $this->args['paging']['offset'],
                'limit' => $this->args['paging']['limit'],
                'totalCount' => (int) $count
            ],
            'items' => $queryResult,
            'timeExecution' => $this->timerEnd - $this->timerStart
        ];
        
        return $returnResult;
    }
    
    public function targetFieldsToMapped($typeFields = [])
    {
        $returnValues = [
            'a.Id'
        ];
        if (count($this->fieldArray) > 0) {
            $returnValues = [];
            foreach ($this->fieldArray as $value) {
                $returnValues[] = $typeFields[$value]['joineryTerm'];
            }
        }
        return $returnValues;
    }
    
    public function hasAccessToModule($module = '', $userRole = ''){
        // HIDE SHOW EMIAL / MOBILE
        $query = $this->default_em->createQueryBuilder();
        $query->select('a');
        $query->from('AppBundle:UserAccess', 'a');
        $query->andWhere('a.Method = :Method');
        $query->andWhere($query->expr()->like('a.Roles', ':Role'));
        $query->setParameters([
            'Method' => $module,
            'Role' => '%'.$userRole.'%'
        ]);
        $results = $query->getQuery()->setFirstResult(0)->setMaxResults(1)->getResult();
        
        if(count($results) > 0){
            $explodeRoles = explode(',', $results[0]->getRoles());
            if(in_array($userRole, $explodeRoles)){
                return true;
            }
            return false;
        }else{
            return false;
        }
        return false;
    }
    
    public function getChartData(){
        // PREPARE QUERY OBJECT
        $this->query = $this->promoted_em->createQueryBuilder();
        
        switch ($this->args['y_axis_type']) {
            case 'SINGLE':
                // SELECT
                $this->query->addSelect($this->args['formula_computation'].'(a.'.$this->args['formula_field'].') AS '.$this->args['y_axis']);
                $this->query->addSelect($this->args['chart_view'].'(a.'.$this->args['x_axis'].') AS '.$this->args['x_axis']);
                // $this->query->addSelect('a.'.$this->args['y_axis'].' AS '.$this->args['y_axis']);
                
                // GROUP AND ORDER BY
                $this->query->groupBy($this->args['x_axis']);
                $this->query->addOrderBy($this->args['x_axis'], 'ASC');
                
                break;
            case 'MULTI':
                // SELECT
                $this->query->addSelect($this->args['formula_computation'].'(a.'.$this->args['formula_field'].') AS '.$this->args['formula_field']);
                $this->query->addSelect($this->args['chart_view'].'(a.'.$this->args['x_axis'].') AS '.$this->args['x_axis']);
                $this->query->addSelect('a.'.$this->args['y_axis'].' AS '.$this->args['y_axis']);
                
                // GROUP AND ORDER BY
                $this->query->groupBy($this->args['x_axis'].', '.$this->args['y_axis']);
                $this->query->addOrderBy($this->args['x_axis'], 'ASC');
                $this->query->addOrderBy($this->args['y_axis'], 'ASC');
                
                break;
            default:
                
        };
        $this->query->from($this->targetEntity, 'a');
        
        // WHERE CLAUSE
        $this->query->andWhere('a.'.$this->args['filter_date'].' BETWEEN :start AND :end');
        $this->query->setParameter('start', $this->args['start_date']->format('Y-m-d 00:00:00'));
        $this->query->setParameter('end', $this->args['end_date']->format('Y-m-d 23:59:59'));
        
        if(isset($this->args['y_axis_filter']) && $this->args['y_axis_filter'] != ''){
            $this->query->andWhere('a.'.$this->args['y_axis'].' IN(:yaxis)');
            $this->query->setParameter('yaxis', explode(',', $this->args['y_axis_filter']));
        }
        
        $this->query->setMaxResults(100);
        $results = $this->query->getQuery()->getResult();
        
        return $results;
    }
    
    public function maskField($fieldValue = ''){
        return (!empty($fieldValue) && strlen($fieldValue) > 3) ? substr($fieldValue, 0, 1).str_repeat('*', strlen($fieldValue) - 2).substr($fieldValue, strlen($fieldValue) - 1, 1) : $fieldValue;
    }
    
    protected function createNotFoundException($message = 'Entity not found')
    {
        return new \Exception($message, 404);
    }

    protected function createInvalidParamsException($message = 'Invalid params')
    {
        return new \Exception($message, 400);
    }

    protected function createAccessDeniedException($message = 'No access to this action')
    {
        return new \Exception($message, 403);
    }

}
