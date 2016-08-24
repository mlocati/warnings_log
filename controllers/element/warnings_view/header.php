<?php
namespace Concrete\Package\WarningsLog\Controller\Element\WarningsView;

use Concrete\Core\Controller\ElementController;

class Header extends ElementController
{
    protected $pkgHandle = 'warnings_log';

    public function getElement()
    {
        return 'warnings_view_header';
    }
}
