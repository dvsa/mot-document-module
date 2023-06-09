<?php

namespace DvsaDocument\Service\Document;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use DvsaDocument\Exceptions\TemplateNotFoundException;
use DvsaDocument\Exceptions\EmptyDocumentException;
use Doctrine\ORM\EntityManager;
use DvsaDocument\Entity\Document;

/**
 * Document storage and retrieval service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DocumentService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Get entity manager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Create a new snapshot based on an array of key/value tuples of document data,
     * a template name, and any optional variations of the template to store
     *
     * @param $templateName
     * @param array $data
     * @param $userId
     * @return int
     * @throws EmptyDocumentException
     * @throws TemplateNotFoundException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createSnapshot($templateName, $userId, $data = [])
    {
        if (empty($data)) {
            throw new EmptyDocumentException('An empty document cannot be created');
        }

        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        // simply map our friendly template name to the currently active
        // template ID which represents it
        $query = $qb->select('t.id')
            ->from('DvsaDocument\Entity\Template', 't')
            ->innerJoin('DvsaDocument\Entity\TemplateType', 'tt', 'WITH', 't.templateType = tt.id')
            ->where('tt.name = :name AND t.isActive = :isActive')
            ->setParameters(['name' => $templateName, 'isActive' => true])
            ->getQuery();

        try {
            $template = $query->getSingleResult();
        } catch (NoResultException $ex) {
            throw new TemplateNotFoundException('Template \'' . $templateName . '\' not found');
        }

        $document = new Document();
        $document->setTemplate($template['id']);
        $document->setDocumentContent($data);
        $document->setCreatedBy($userId)->setCreatedOn((new \DateTime()));

        $em->persist($document);
        $em->flush();

        return $document->getId();
    }

    public function updateSnapshot(Document $document)
    {
        $em = $this->getEntityManager();
        $em->persist($document);
        $em->flush($document);
    }

    /**
     * Retrieve a report name for a given document identifier, optionally selecting
     * a variation of the original template stored against the document
     *
     * @param int    $documentId
     * @param string $variation
     *
     * @return string
     *
     * @throws TemplateNotFoundException
     */
    public function getReportName($documentId, $variation = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        if (is_null($variation)) {
            $qb->select('t.jasperReportName')
                ->from('DvsaDocument\Entity\Document', 'd')
                ->innerJoin('DvsaDocument\Entity\Template', 't', 'WITH', 'd.template = t.id')
                ->where('d.id = ?0');

            $params = array($documentId);
        } else {
            $qb->select('tv.jasperReportName')
                ->from('DvsaDocument\Entity\Document', 'd')
                ->innerJoin('DvsaDocument\Entity\Template', 't', 'WITH', 'd.template = t.id')
                ->innerJoin('DvsaDocument\Entity\TemplateVariation', 'tv', 'WITH', 'tv.template = t.id')
                ->where('d.id = ?0 AND tv.name = ?1');

            $params = array($documentId, $variation);
        }

        $qb->setParameters($params);

        $query = $qb->getQuery();

        try {
            $result = $query->getSingleResult();
        } catch (NoResultException $ex) {
            throw new TemplateNotFoundException('Template not found');
        }

        return $result['jasperReportName'];
    }

    /**
     * Delete document by id
     *
     * @param $id
     * @throws EmptyDocumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function deleteSnapshot($id)
    {
        $em = $this->getEntityManager();
        $document = $em->find('DvsaDocument\Entity\Document', $id);

        if (!$document) {
            /** @BUG Clearly the problem is not that the document is empty */
            throw new EmptyDocumentException('No document to delete');
        }

        $em->remove($document);
        $em->flush();
    }

    /**
     * Obtain a document by its Id
     *
     * @param integer $id The ID of the cert document snapshot data row
     *
     * @return \DvsaDocument\Entity\Document
     * @throws EmptyDocumentException
     */
    public function getSnapshotById($id)
    {
        $document = $this->getEntityManager()->find('DvsaDocument\Entity\Document', $id);

        if (!$document) {
            throw new EmptyDocumentException('Unable to locate cert document data by id: ' . $id);
        }

        return $document;

    }
}
