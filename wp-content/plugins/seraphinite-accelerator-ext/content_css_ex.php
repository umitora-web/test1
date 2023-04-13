<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

class CssToXPathNormalizedAttributeMatchingExtension extends Symfony\Component\CssSelector\XPath\Extension\AttributeMatchingExtension
{

	public function translateEquals( Symfony\Component\CssSelector\XPath\XPathExpr $xpath, string $attribute, ?string $value ) : Symfony\Component\CssSelector\XPath\XPathExpr
	{
		if( $attribute === '@class' )
			$value = '| ' . $value . ' |';
		return( parent::translateEquals( $xpath, $attribute, $value ) );
	}

	public function translatePrefixMatch( Symfony\Component\CssSelector\XPath\XPathExpr $xpath, string $attribute, ?string $value ) : Symfony\Component\CssSelector\XPath\XPathExpr
	{
		if( $attribute === '@class' )
			$value = '| ' . $value;
		return( parent::translatePrefixMatch( $xpath, $attribute, $value ) );
	}

	public function translateSuffixMatch( Symfony\Component\CssSelector\XPath\XPathExpr $xpath, string $attribute, ?string $value ) : Symfony\Component\CssSelector\XPath\XPathExpr
	{
		if( $attribute === '@class' )
			$value = $value . ' |';
		return( parent::translateSuffixMatch( $xpath, $attribute, $value ) );
	}

	public function translateDifferent( Symfony\Component\CssSelector\XPath\XPathExpr $xpath, string $attribute, ?string $value ) : Symfony\Component\CssSelector\XPath\XPathExpr
	{
		if( $attribute === '@class' )
			$value = '| ' . $value . ' |';
		return( parent::translateDifferent( $xpath, $attribute, $value ) );
	}

	public function translateIncludes( Symfony\Component\CssSelector\XPath\XPathExpr $xpath, string $attribute, ?string $value ) : Symfony\Component\CssSelector\XPath\XPathExpr
	{
		if( $attribute === '@class' )
			return( $xpath -> addCondition( $value ? sprintf(
				'%1$s and contains(%1$s, %2$s)',
				$attribute,
				Symfony\Component\CssSelector\XPath\Translator::getXpathLiteral( ' ' . $value . ' ' )
			) : '0' ) );

		return( parent::translateIncludes( $xpath, $attribute, $value ) );
	}

	public function translateDashMatch( Symfony\Component\CssSelector\XPath\XPathExpr $xpath, string $attribute, ?string $value ) : Symfony\Component\CssSelector\XPath\XPathExpr
	{
		if( $attribute === '@class' )
			return( $xpath -> addCondition( sprintf(
				'%1$s and (%1$s = %2$s or starts-with(%1$s, %3$s))',
				$attribute,
				Symfony\Component\CssSelector\XPath\Translator::getXpathLiteral( '| ' . $value . ' |' ),
				Symfony\Component\CssSelector\XPath\Translator::getXpathLiteral( '| ' . $value . '-' )
			) ) );

		return( parent::translateDashMatch( $xpath, $attribute, $value ) );
	}
}

class CssToXPathHtmlExtension extends Symfony\Component\CssSelector\XPath\Extension\HtmlExtension
{
	public function translateHover(Symfony\Component\CssSelector\XPath\XPathExpr $xpath): Symfony\Component\CssSelector\XPath\XPathExpr
	{
		return $xpath -> addCondition('ANYSTATE or 1');
	}

	public function translateInvalid(Symfony\Component\CssSelector\XPath\XPathExpr $xpath): Symfony\Component\CssSelector\XPath\XPathExpr
	{
		return $this -> translateHover($xpath);
	}

	public function translateVisited(Symfony\Component\CssSelector\XPath\XPathExpr $xpath): Symfony\Component\CssSelector\XPath\XPathExpr
	{
		return $this -> translateHover($xpath);
	}
}

