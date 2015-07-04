<?php
namespace Minibus\Model\Repository;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use Minibus\Model\Entity\Process;

class AlertRepository extends EntityRepository
{

    /**
     *
     * @return array
     */
    public function getAllIdObjects(array $processIdentifiers = array())
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        
        $qb->select(array(
            "alert.idobject"
        ))->from('\Minibus\Model\Entity\Alert', 'alert');
        if (count($processIdentifiers > 0))
            $qb->join("alert.execution", "execution")->join("execution.process", "process");
        $first = true;
        $count = 0;
        foreach ($processIdentifiers as $processIdentifier) {
            $clause = "process.mode=:mode$count AND process.type=:type$count AND process.endpoint=:endpoint$count";
            if ($count == 0)
                $qb->where($clause);
            else
                $qb->orWhere($clause);
            $qb->setParameter(":mode$count", $processIdentifier['mode']);
            $qb->setParameter(":type$count", $processIdentifier['type']);
            $qb->setParameter(":endpoint$count", $processIdentifier['endpoint']);
            $count ++;
        }
        $alerts = $qb->getQuery()->getResult();
        return array_map(function ($alert)
        {
            if (is_array($alert) && array_key_exists('idobject', $alert))
                return $alert['idobject'];
        }, $alerts);
    }

    /**
     *
     * @param Process $process            
     * @return array
     */
    public function getAlertsForProcess(array $processIdentifiers, array $selectedLevels, $hydrateArray = true)
    {
        if (! is_array($processIdentifiers) || count($processIdentifiers) == 0)
            return array();
        $qb = $this->getEntityManager()->createQueryBuilder();
        if ($hydrateArray)
            $qb->select(array(
                "alert.id",
                "alert.date",
                "alert.level",
                "alert.message",
                "execution.id as execution_id",
                "process.mode",
                "process.type",
                "process.endpoint",
                "process.type",
                "process.annee"
            ));
        else
            $qb->select('alert');
        $qb->from('\Minibus\Model\Entity\Alert', 'alert')
            ->join("alert.execution", "execution")
            ->join("execution.process", "process");
        $first = true;
        $count = 0;
        foreach ($processIdentifiers as $processIdentifier) {
            $clause = "process.mode=:mode$count AND process.type=:type$count AND process.endpoint=:endpoint$count";
            if ($count == 0)
                $qb->where($clause);
            else
                $qb->orWhere($clause);
            $qb->setParameter(":mode$count", $processIdentifier['mode']);
            $qb->setParameter(":type$count", $processIdentifier['type']);
            $qb->setParameter(":endpoint$count", $processIdentifier['endpoint']);
            $count ++;
        }
        $levels = array_map('\Minibus\Model\Repository\AlertRepository::getAlertLevels', $selectedLevels);
        $qb->andWhere('alert.level IN (:levels)')->setParameter('levels', $levels);
        $alerts = $qb->getQuery()->getResult();
        return $alerts;
    }

    /**
     *
     * @param int $level            
     * @return string|NULL
     */
    private static function getAlertLevels($level)
    {
        switch ($level) {
            case "1":
                return 'ALERT';
                break;
            case "3":
                return 'ERROR';
                break;
            case "4":
                return 'WARNING';
                break;
        }
        return null;
    }
}
