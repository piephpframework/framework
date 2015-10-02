<?php

namespace Object69\Modules\Tpl69;

use DOMDocument;

/**
 * @property Tpl $tpl Link to the template object
 * @property DOMDocument $doc Link to the dom document
 */
class TplAttr{

    public
            $tpl        = null,
            $doc        = null,
            $type       = null,
            $value      = null,
            $attributes = null,
            $offset     = 0;

}
