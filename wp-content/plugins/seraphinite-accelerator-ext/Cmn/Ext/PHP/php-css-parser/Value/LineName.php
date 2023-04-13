<?php

namespace seraph_accel\Sabberworm\CSS\Value;

use seraph_accel\Sabberworm\CSS\Parsing\ParserState;
use seraph_accel\Sabberworm\CSS\Parsing\UnexpectedTokenException;
use seraph_accel\Sabberworm\CSS\Settings;

class LineName extends ValueList {
	public function __construct($aComponents = array(), $iLineNo = 0) {
		parent::__construct($aComponents, ' ', $iLineNo);
	}

	public static function parse(ParserState $oParserState) {
		$oParserState->consume('[');
		$oParserState->consumeWhiteSpace();
		$aNames = array();
		do {
			if($oParserState->getSettings()->bLenientParsing & Settings::ParseErrMed) {
				try {
					$aNames[] = $oParserState->parseIdentifier();
				} catch(UnexpectedTokenException $e) {}
			} else {
				$aNames[] = $oParserState->parseIdentifier();
			}
			$oParserState->consumeWhiteSpace();
		} while (!$oParserState->comes(']'));
		$oParserState->consume(']');
		return new LineName($aNames, $oParserState->currentLine());
	}



	public function __toString() {
		return $this->render(new \seraph_accel\Sabberworm\CSS\OutputFormat());
	}

	public function render(\seraph_accel\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		return '[' . parent::render(\seraph_accel\Sabberworm\CSS\OutputFormat::createCompact()) . ']';
	}

}
