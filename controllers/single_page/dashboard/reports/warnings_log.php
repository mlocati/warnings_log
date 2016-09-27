<?php
namespace Concrete\Package\WarningsLog\Controller\SinglePage\Dashboard\Reports;

use PDO;
use Exception;
use Concrete\Core\Page\Controller\DashboardPageController;
use Application\Concrete\Util\WhoopsStorage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Concrete\Core\Asset\Asset;

defined('C5_EXECUTE') or die('Access Denied.');

class WarningsLog extends DashboardPageController
{
    public function view()
    {
        $asset = \AssetList::getInstance()->register('javascript', 'warnings_log', 'js/warnings_log.js', ['combine' => !true, 'minify' => !true, 'position' => Asset::ASSET_POSITION_FOOTER], 'warnings_log');
        $this->requireAsset($asset);
        $header = new \Concrete\Package\WarningsLog\Controller\Element\WarningsView\Header();
        $this->set('headerMenu', $header);
        $this->set('warningsList', $this->loadWarningsList());
    }

    private function loadWarningsList()
    {
        $storage = new WhoopsStorage();
        $cn = $storage->getConnection(false);
        $rows = [];
        if ($cn) {
            $dh = $this->app->make('helper/date');
            /* @var \Concrete\Core\Localization\Service\Date $dh */
            $cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $rs = $cn->query('select * from warnings order by lastSeen DESC');
            while (($row = $rs->fetch(PDO::FETCH_ASSOC)) !== false) {
                $row['id'] = (int) $row['id'];
                $row['line'] = $row['line'] ? (int) $row['line'] : null;
                $dt = new \DateTime($row['firstSeen']);
                $row['firstSeen'] = $dt->getTimestamp();
                $row['firstSeen__view'] = $dh->formatDateTime($dt, false, true);
                $dt = new \DateTime($row['lastSeen']);
                $row['lastSeen'] = $dt->getTimestamp();
                $row['lastSeen__view'] = $dh->formatDateTime($dt, false, true);
                $row['numSeen'] = (int) $row['numSeen'];
                $row['hide'] = (bool) $row['hide'];
                $rows[] = $row;
            }
            $rs->closeCursor();
        }

        return $rows;
    }

    public function get_warnings_list()
    {
        if (!$this->request->isPost()) {
            $this->view();

            return;
        }
        try {
            if (!$this->token->validate('get_warnings_list')) {
                throw new Exception($this->token->getErrorMessage());
            }
            $list = $this->loadWarningsList();

            return new JsonResponse($list);
        } catch (Exception $x) {
            return new JsonResponse(['error' => $x->getMessage()], 400);
        }
    }

    public function bulk_operation()
    {
        if (!$this->request->isPost()) {
            $this->view();

            return;
        }
        try {
            if (!$this->token->validate('bulk_operation')) {
                throw new Exception($this->token->getErrorMessage());
            }
            $ids = [];
            $idsReceived = $this->request->post('itemIDs');
            if (is_array($idsReceived)) {
                foreach ($idsReceived as $idReceived) {
                    if (is_string($idReceived) && is_numeric($idReceived)) {
                        $ids[] = (int) $idReceived;
                    }
                }
            }
            if (empty($ids)) {
                throw new Exception(t('Invalid parameter: %s', 'itemIDs'));
            }
            $operation = $this->request->post('operation');
            switch ($operation) {
                case 'hide':
                    $sql = 'update warnings set hide = 1';
                    $msg = t2('%d element has been hidden', '%d elements have been hidden', count($ids));
                    break;
                case 'show':
                    $sql = 'update warnings set hide = 0';
                    $msg = t2('%d element has been shown', '%d elements have been shown', count($ids));
                    break;
                case 'delete':
                    $sql = 'delete from warnings';
                    $msg = t2('%d element has been deleted', '%d elements have been deleted', count($ids));
                    break;
                default:
                    throw new Exception(t('Invalid parameter: %s', 'operation'.$operation));
            }
            $storage = new WhoopsStorage();
            $cn = $storage->getConnection(true);
            $cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $cn->query($sql.' where id = '.implode(' or id = ', $ids));
            if ($operation === 'delete') {
                $cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $cn->query('VACUUM');
            }

            return new JsonResponse($msg);
        } catch (Exception $x) {
            return new JsonResponse(['error' => $x->getMessage()], 400);
        }
    }
}
