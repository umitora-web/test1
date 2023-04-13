<?php

namespace seraph_accel\Sabberworm\CSS\Value;

use seraph_accel\Sabberworm\CSS\Parsing\ParserState;

class Color extends CSSFunction {

	protected $bIsExpr;

	public function __construct($aColor, $iLineNo = 0, $bIsExpr = false, $sSeparator = ',') {
		parent::__construct(implode('', array_keys($aColor)), $aColor, $sSeparator, $iLineNo);
		$this->bIsExpr = $bIsExpr;
	}

	public static function parse(ParserState $oParserState) {
		$aColor = array();
		$bIsExpr = false;
		$sSeparator = ',';
		$nSeps = 0;
		if ($oParserState->comes('#')) {
			$oParserState->consume('#');
			$sValue = $oParserState->parseIdentifier(false);
			if ($oParserState->strlen($sValue) === 3) {
				$sValue = $sValue[0] . $sValue[0] . $sValue[1] . $sValue[1] . $sValue[2] . $sValue[2];
			} else if ($oParserState->strlen($sValue) === 4) {
				$sValue = $sValue[0] . $sValue[0] . $sValue[1] . $sValue[1] . $sValue[2] . $sValue[2] . $sValue[3] . $sValue[3];
			}

			if ($oParserState->strlen($sValue) === 8) {
				$aColor = array(
					'r' => new Size(intval($sValue[0] . $sValue[1], 16), null, true, $oParserState->currentLine()),
					'g' => new Size(intval($sValue[2] . $sValue[3], 16), null, true, $oParserState->currentLine()),
					'b' => new Size(intval($sValue[4] . $sValue[5], 16), null, true, $oParserState->currentLine()),
					'a' => new Size(round(self::mapRange(intval($sValue[6] . $sValue[7], 16), 0, 255, 0, 1), 2), null, true, $oParserState->currentLine())
				);
			} else {
				$aColor = array(
					'r' => new Size(intval(@$sValue[0] . @$sValue[1], 16), null, true, $oParserState->currentLine()),
					'g' => new Size(intval(@$sValue[2] . @$sValue[3], 16), null, true, $oParserState->currentLine()),
					'b' => new Size(intval(@$sValue[4] . @$sValue[5], 16), null, true, $oParserState->currentLine())
				);
			}
		} else {
			$sColorMode = $oParserState->parseIdentifier(true);
			$oParserState->consumeWhiteSpace();
			$oParserState->consume('(');
			for ($i = 0; ; ++$i) {
				$oParserState->consumeWhiteSpace();
				if ($oParserState->comes(')'))
					break;

				$szLen = $oParserState->currentPos();
				if( $oParserState->comes('var(', true) )
				{
					$sz = Value::parseIdentifierOrFunction($oParserState);
					$bIsExpr = true;
				}
				else if( Value::shouldParseCalcFunction($oParserState) )
				{
					$sz = CalcFunction::parse($oParserState);
					$bIsExpr = true;
				}
				else
					$sz = Size::parse($oParserState, true);
				$szLen = $oParserState->currentPos() - $szLen;

				if ($i < 4)
				{
					if ($i == $oParserState->strlen($sColorMode))
						$sColorMode .= 'a';
					$aColor[$sColorMode[$i]] = $sz;
				}

				$oParserState->consumeWhiteSpace();
				if ($oParserState->comes(')'))
					break;

				if ($oParserState->comes(',') || $oParserState->comes('/'))
				{
					$nSeps++;
					$sSeparator = $oParserState->peek();
					$oParserState->consume(1);
				}
				else if (!$szLen)
					$oParserState->consume(1);
				else
					$nSeps++;
			}
			$oParserState->consume(')');
		}

		if ($nSeps > 1)
			$sSeparator = ',';

		$clr = new Color($aColor, $oParserState->currentLine(), $bIsExpr, $sSeparator);
		if (strlen($clr->getName()) < 3)
			$clr->setName($sColorMode);
		return $clr;
	}

	private static function mapRange($fVal, $fFromMin, $fFromMax, $fToMin, $fToMax) {
		$fFromRange = $fFromMax - $fFromMin;
		$fToRange = $fToMax - $fToMin;
		$fMultiplier = $fToRange / $fFromRange;
		$fNewVal = $fVal - $fFromMin;
		$fNewVal *= $fMultiplier;
		return $fNewVal + $fToMin;
	}

	public function getColor() {
		return $this->aComponents;
	}

	public function setColor($aColor) {
		$this->setName(implode('', array_keys($aColor)));
		$this->aComponents = $aColor;
	}

	public function getColorDescription() {
		return $this->getName();
	}

	public function __toString() {
		return $this->render(new \seraph_accel\Sabberworm\CSS\OutputFormat());
	}

	public function render(\seraph_accel\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		// Shorthand RGB color values
		if(!$this->bIsExpr && $oOutputFormat->getRGBHashNotation() && implode('', array_keys($this->aComponents)) === 'rgb') {
			$sResult = sprintf(
				'%02x%02x%02x',
				$this->aComponents['r']->getSize(),
				$this->aComponents['g']->getSize(),
				$this->aComponents['b']->getSize()
			);
			return '#'.(($sResult[0] == $sResult[1]) && ($sResult[2] == $sResult[3]) && ($sResult[4] == $sResult[5]) ? "$sResult[0]$sResult[2]$sResult[4]" : $sResult);
		}
		return parent::render($oOutputFormat);
	}
}
