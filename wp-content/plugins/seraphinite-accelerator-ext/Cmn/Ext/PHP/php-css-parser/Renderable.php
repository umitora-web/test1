<?php

namespace seraph_accel\Sabberworm\CSS;

interface Renderable {
	public function __toString();
	public function render(\seraph_accel\Sabberworm\CSS\OutputFormat $oOutputFormat);
	public function getLineNo();
}