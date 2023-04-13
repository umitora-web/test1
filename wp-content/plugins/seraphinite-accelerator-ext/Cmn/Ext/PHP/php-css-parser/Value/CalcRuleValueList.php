<?php

namespace seraph_accel\Sabberworm\CSS\Value;

class CalcRuleValueList extends RuleValueList {
	public function __construct($iLineNo = 0) {
		parent::__construct(array(), ',', $iLineNo);
	}

	public function render(\seraph_accel\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		return $oOutputFormat->implode(' ', $this->aComponents);
	}

}
