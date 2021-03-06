<?php

namespace Pie\Modules\Tpl;

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
