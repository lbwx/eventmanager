<?php

namespace WxSecurityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class WxSecurityBundle extends Bundle {
	public function getParent() {
		return 'FOSUserBundle';
	}
}
