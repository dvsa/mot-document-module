<?php

/**
 * Report Name Controller
 * Interact with the Document Service to determine the report name of a given document id
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace DvsaDocument\Controller;

use Laminas\Http\Response;
use Laminas\Mvc\Controller\Plugin\Params;
use DvsaDocument\Exceptions\TemplateNotFoundException;
use Laminas\View\Model\JsonModel;

/**
 * Report Name Controller
 * Interact with the Document Service to determine the report name of a given document id
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ReportNameController extends AbstractDocumentController
{
    /**
     * Return a single document resource
     *
     * @return Response|JsonModel
     */
    public function getAction()
    {
        $id = $this->getId();
        $variation = $this->getVariation();

        try {
            if (is_null($id)) {
                return $this->respond('Document ID is expected', Response::STATUS_CODE_417);
            }

            $documentService = $this->getDocumentService();
            $reportName = $documentService->getReportName($id, $variation);

            return $this->respondWithJson(array('report-name' => $reportName));
        } catch (TemplateNotFoundException $ex) {
            return $this->respond('Document not found', Response::STATUS_CODE_404);
        } catch (\Exception $ex) {
            return $this->respond($ex->getMessage(), Response::STATUS_CODE_500);
        }
    }

    /**
     * Get the variation from route or the query
     *
     * @return string
     */
    private function getVariation()
    {
        /** @var Params $params */
        $params = $this->params();
        /** @var string|null */
        $variation = $params->fromRoute('variation');

        if (is_null($variation)) {
            /** @var string */
            $variation = $params->fromQuery('variation');
        }

        return $variation;
    }
}
