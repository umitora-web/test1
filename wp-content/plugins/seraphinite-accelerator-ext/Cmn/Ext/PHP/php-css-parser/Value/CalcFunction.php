<?php

namespace seraph_accel\Sabberworm\CSS\Value;

use seraph_accel\Sabberworm\CSS\Parsing\ParserState;
use seraph_accel\Sabberworm\CSS\Parsing\UnexpectedTokenException;

class CalcFunction extends CSSFunction {
	const T_OPERAND  = 1;
	const T_OPERATOR = 2;

	public static function parse(ParserState $oParserState) {
		$aOperators = array('+', '-', '*', '/');
		$sFunction = trim($oParserState->consumeUntil('(', false, true));
		$oList = new RuleValueList(',', $oParserState->currentLine());

		for (;;) {
			$oCalcList = new CalcRuleValueList($oParserState->currentLine());
			$iNestingLevel = 0;
			$iLastComponentType = NULL;
			for (;;) {
				$oParserState->consumeWhiteSpace();
				if ($oParserState->comes('(')) {
					$iNestingLevel++;
					$oCalcList->addListComponent($oParserState->consume(1));
					continue;
				} else if ($oParserState->comes(')')) {
					$iNestingLevel--;
					if ($iNestingLevel < 0)
						break;
					$oCalcList->addListComponent($oParserState->consume(1));
					continue;
				} else if ($oParserState->comes(',') && !$iNestingLevel) {
					break;
				}
				if ($iLastComponentType != CalcFunction::T_OPERAND) {
					$oVal = Value::parsePrimitiveValue($oParserState);
					$oCalcList->addListComponent($oVal);
					$iLastComponentType = CalcFunction::T_OPERAND;
				} else {
					if (in_array($oParserState->peek(), $aOperators)) {
						if (($oParserState->comes('-') || $oParserState->comes('+'))) {
							if ($oParserState->peek(1, -1) != ' ' || !($oParserState->comes('- ') || $oParserState->comes('+ '))) {
								throw new UnexpectedTokenException(" {$oParserState->peek()} ", $oParserState->peek(1, -1) . $oParserState->peek(2), 'literal', $oParserState->currentLine());
							}
						}
						$oCalcList->addListComponent($oParserState->consume(1));
						$iLastComponentType = CalcFunction::T_OPERATOR;
					} else {
						throw new UnexpectedTokenException(
							sprintf(
								'Next token was expected to be an operand of type %s. Instead "%s" was found.',
								implode(', ', $aOperators),
								$oVal
							),
							'',
							'custom',
							$oParserState->currentLine()
						);
					}
				}
			}
			$oList->addListComponent($oCalcList);

			if (!$oParserState->comes(','))
				break;
			$oParserState->consume(',');
		}

		$oParserState->consume(')');
		return new CalcFunction($sFunction, $oList, ',', $oParserState->currentLine());
	}

}
