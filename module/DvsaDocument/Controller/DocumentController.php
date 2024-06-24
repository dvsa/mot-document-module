<?php

/**
 * Document Controller
 * Interact with the Document Service to create and delete document
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace DvsaDocument\Controller;

use DvsaDocument\Exceptions\TemplateNotFoundException;
use Laminas\Mvc\Controller\Plugin\Params;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;

/**
 * Document Controller
 * Interact with the Document Service to create and delete document
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class DocumentController extends AbstractDocumentController
{
    /**
     * Create new document and return ID
     *
     * @return Response|JsonModel
     */
    public function createAction()
    {
        /** @var Params $params */
        $params = $this->params();
        /** @var array|null */
        $data = $params->fromPost('data');
        /** @var string|null */
        $templateName = $params->fromPost('template');
        /** @var int */
        $userId = $params->fromPost('userId');

        if (empty($userId)) {
            return $this->respond('Can\'t create an document (userid undefined)', Response::STATUS_CODE_500);
        }

        if (is_null($data)) {
            return $this->respond('Can\'t create an empty document', Response::STATUS_CODE_500);
        }

        if (is_null($templateName)) {
            return $this->respond('Template name is expected', Response::STATUS_CODE_500);
        }

        try {
            $documentId = $this->getDocumentService()->createSnapshot($templateName, $userId, $data);
            return $this->respondWithJson(array('id' => $documentId));
        } catch (TemplateNotFoundException $ex) {
            return $this->respond('Template ' . $templateName . ' not found', Response::STATUS_CODE_404);
        } catch (\Exception $ex) {
            return $this->respond($ex->getMessage(), Response::STATUS_CODE_500);
        }
    }

    /**
     * Delete document by ID
     *
     * @return Response|JsonModel
     */
    public function deleteAction()
    {
        $id = $this->getId();

        if (is_null($id)) {
            return $this->respond('Document ID is expected', Response::STATUS_CODE_417);
        }

        try {
            $this->getDocumentService()->deleteSnapshot($id);
            return $this->respondWithJson(array('id' => $id));
        } catch (\Exception $ex) {
            return $this->respond($ex->getMessage(), Response::STATUS_CODE_500);
        }
    }
}
