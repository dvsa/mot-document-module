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
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;

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
     * @param $data
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
     * @return int
     */
    public function getId()
    {
        $id = $this->params()->fromRoute('id');

        if (is_null($id)) {
            $id = $this->params()->fromQuery('id');
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
