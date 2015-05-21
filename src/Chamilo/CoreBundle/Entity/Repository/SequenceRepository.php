<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\CoreBundle\Entity\Repository;

use Chamilo\CoreBundle\Entity\SequenceResource;
use Doctrine\ORM\EntityRepository;
use Fhaculty\Graph\Vertex;

/**
 * Class SequenceRepository
 * The functions inside this class should return an instance of QueryBuilder
 *
 * @package Chamilo\CoreBundle\Entity\Repository
 */
class SequenceRepository extends EntityRepository
{
    /**
     * Find the SequenceResource based in the resourceId and type
     * @param int $resourceId
     * @param int $type
     *
     * @return SequenceResource
     */
    public function findRequirementForResource($resourceId, $type)
    {
  /*      $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("resourceId", $resourceId))
            ->andWhere(Criteria::expr()->eq("type", $type));
*/
        return $this->findOneBy(['resourceId' => $resourceId, 'type' => $type]);
    }

    /**
     * Deletes a node and check in all the dependencies if the node exists in
     * order to deleted.
     *
     * @param int $resourceId
     * @param int $type
     */
    public function deleteResource($resourceId, $type)
    {
        $sequence = $this->findRequirementForResource($resourceId, $type);

        if ($sequence && $sequence->hasGraph()) {
            $em = $this->getEntityManager();
            $graph = $sequence->getUnserializeGraph();

            $mainVertex = $graph->getVertex($resourceId);
            $vertices = $graph->getVertices();

            /** @var Vertex $vertex */
            foreach ($vertices as $vertex) {
                $subResourceId = $vertex->getId();
                $subSequence = $this->findRequirementForResource($subResourceId, $type);
                if ($sequence && $subSequence->hasGraph()) {
                    $graph = $subSequence->getUnserializeGraph();
                    $subMainVertex = $graph->getVertex($resourceId);
                    $subMainVertex->destroy();
                    $subSequence->setGraphAndSerialize($graph);
                    $em->persist($subSequence);
                }
            }
            $mainVertex->destroy();

            $em->remove($sequence);
            $em->flush();
        }
    }

}
