<?php

namespace seraph_accel\Sabberworm\CSS\Value;

abstract class ValueList extends Value {

	protected $aComponents;
	protected $sSeparator;

	public function __construct($aComponents = array(), $sSeparator = ',', $iLineNo = 0) {
		parent::__construct($iLineNo);
		if (!is_array($aComponents)) {
			$aComponents = array($aComponents);
		}
		$this->aComponents = $aComponents;
		$this->sSeparator = $sSeparator;
	}

	public function addListComponent($mComponent) {
		$this->aComponents[] = $mComponent;
	}

	public function getListComponents() {
		return $this->aComponents;
	}

	public function setListComponents($aComponents) {
		$this->aComponents = $aComponents;
	}

	public function getListSeparator() {
		return //is_array( $this->sSeparator ) ? $this->sSeparator[ 0 ] : 
			$this->sSeparator;
	}

	public function setListSeparator($sSeparator) {
		$this->sSeparator = $sSeparator;
	}

	public function __toString() {
		return $this->render(new \seraph_accel\Sabberworm\CSS\OutputFormat());
	}

	public function render(\seraph_accel\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		//if( !is_array( $this->sSeparator ) )
			return $oOutputFormat->implode($oOutputFormat->spaceBeforeListArgumentSeparator($this->sSeparator) . $this->sSeparator . $oOutputFormat->spaceAfterListArgumentSeparator($this->sSeparator), $this->aComponents);

		//$res = '';
		//$iSep = 0;
		//foreach( $this->aComponents as $component )
		//{
		//    if( $res )
		//    {
		//        $sSeparator = $this->sSeparator[ $iSep ];
		//        $res .= $oOutputFormat->spaceBeforeListArgumentSeparator($sSeparator) . $sSeparator . $oOutputFormat->spaceAfterListArgumentSeparator($sSeparator);
		//        if( $iSep + 1 < count( $this->sSeparator ) )
		//            $iSep++;
		//    }

		//    $res .= $component;
		//}

		//return $res;
	}

}
