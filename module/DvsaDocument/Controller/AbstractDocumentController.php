<?php

/**
 * Abstract Document Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace DvsaDocument\Controller;

use DvsaDocument\Service\Document\DocumentService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Controller\Plugin\Params;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;
use ArrayAccess;
use Traversable;

/**
 * Abstract Document Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class AbstractDocumentController extends AbstractActionController
{
    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /** @var DocumentService */
    protected $documentService;

    /**
     * @param null|array|Traversable|ArrayAccess $data
     *
     * @return JsonModel
     */
    public function respondWithJson($data)
    {
        return new JsonModel($data);
    }

    /**
     * Generate the response
     *
     * @param string $content
     * @param int $statusCode
     * @return \Laminas\Http\Response
     */
    public function respond($content, $statusCode)
    {
        $response = new Response();
        $response->setContent($content);
        $response->setStatusCode($statusCode);

        return $response;
    }

    /**
     * Get the id from route or the query
     *
     * @return int|null
     */
    public function getId()
    {
        /** @var Params $params */
        $params = $this->params();
        /** @var int|null */
        $id = $params->fromRoute('id');

        if (is_null($id)) {
        /** @var int|null */
            $id = $params->fromQuery('id');
        }

        return $id;
    }

    /**
     * @return DocumentService
     */
    public function getDocumentService(): DocumentService
    {
        return $this->documentService;
    }

    /**
     * @param DocumentService $documentService
     * @return $this
     */
    public function setDocumentService(DocumentService $documentService)
    {
        $this->documentService = $documentService;
        return $this;
    }
}
