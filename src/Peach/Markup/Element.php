<?php
/*
 * Copyright (c) 2013 @trashtoy
 * https://github.com/trashtoy/
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the
 * Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
/** @package Markup */
/**
 * マークアップ言語の要素を表現するクラスです.
 *
 * @package Markup
 */
abstract class Peach_Markup_Element implements Peach_Markup_Node
{
    /**
     * この要素の名前です.
     * @var string
     */
    private $name;
    
    /**
     * この要素に含まれる属性です.
     * @var Peach_Util_Map
     */
    private $attributes;
    
    /**
     * 指定された要素名を持つ Element を構築します.
     * 
     * @param string $name 要素名
     * @throws InvalidArgumentException 要素名が空文字列だった場合
     */
    public function __construct($name)
    {
        $this->name       = self::cleanNameString($name);
        $this->attributes = new Peach_Util_ArrayMap();
    }
    
    /**
     * 指定された文字列について, 不正な UTF-8 のバイト列をあらかじめ除去した上で,
     * 要素名または属性名として適切かどうかを調べます.
     * 
     * @param  string $name 要素名
     * @return string       不正なシーケンスを除去した結果の文字列
     * @throws InvalidArgumentException 引数が要素名または属性名として不適切な場合
     */
    private static function cleanNameString($name)
    {
        if (!strlen($name)) {
            throw new InvalidArgumentException("Empty string specified");
        }
        $cleanName = self::cleanString($name);
        if (!Peach_Markup_NameValidator::validate($cleanName)) {
            throw new InvalidArgumentException("'{$cleanName}' is not a valid name");
        }
        return $cleanName;
    }
    
    /**
     * 引数の文字列から UTF-8 として不適切なシーケンスを除去します.
     * 
     * @param  string $var 文字列
     * @return string      不正なシーケンスを除去した結果
     */
    private static function cleanString($var)
    {
        // @codeCoverageIgnoreStart
        static $utf8Codec = null;
        if ($utf8Codec === null) {
            $utf8Codec = new Peach_DF_Utf8Codec();
        }
        // @codeCoverageIgnoreEnd
        
        $str = Peach_Util_Values::stringValue($var);
        return $utf8Codec->encode($utf8Codec->decode($str));
    }
    
    /**
     * この要素の名前を返します.
     * @return string この要素の名前
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * 指定された属性の値を返します.
     * 属性が存在しないか, 値の省略された属性の場合は NULL を返します.
     * 属性が存在しているかどうかを調べる場合は 
     * {@link Peach_Markup_Element::hasAttribute() hasAttribute()} 
     * を使用してください.
     * 
     * @param  string $name 属性名
     * @return string       属性の値. 存在しないか, 値の省略された属性の場合は NULL
     */
    public function getAttribute($name)
    {
        return $this->attributes->get($name);
    }
    
    /**
     * 指定された属性が存在するかどうかを調べます.
     * @param  string $name 属性名
     * @return bool         属性が存在する場合は TRUE, それ以外は FALSE
     */
    public function hasAttribute($name)
    {
        return $this->attributes->containsKey($name);
    }
    
    /**
     * この要素に属性を設定します.
     * $value が設定されていない場合は, 値が省略された属性を追加します.
     * 
     * @param string $name  属性名
     * @param string $value 属性値
     */
    public function setAttribute($name, $value = null)
    {
        $cleanName  = self::cleanNameString($name);
        $cleanValue = isset($value) ? self::cleanString($value) : null;
        $this->attributes->put($cleanName, $cleanValue);
    }
    
    /**
     * この要素に複数の属性を一括して設定します.
     * <code>$element->setAttributes(array("id" => "foo", "class" => "bar"));</code>
     * のように, キーに属性名, 値に属性の値を指定してください.
     * 
     * キーが省略された場合 (具体的にはキーに整数が指定された場合) は,
     * その値を属性名とする Boolean 属性を設定します.
     * 
     * @param array|Peach_Util_ArrayMap $attr 属性の一覧
     */
    public function setAttributes($attr)
    {
        if ($attr instanceof Peach_Util_ArrayMap) {
            $this->setAttributes($attr->asArray());
            return;
        }
        if (!is_array($attr)) {
            throw new InvalidArgumentException("Array required.");
        }
        foreach ($attr as $key => $value) {
            if (is_numeric($key)) {
                $attrName  = $value;
                $attrValue = null;
            } else {
                $attrName  = $key;
                $attrValue = $value;
            }
            $this->setAttribute($attrName, $attrValue);
        }
    }
    
    /**
     * 指定された属性を削除します.
     * @param string $name 属性名
     */
    public function removeAttribute($name)
    {
        $this->attributes->remove($name);
    }
    
    /**
     * この要素が持つすべての属性を配列で返します.
     * この要素の返り値を, そのまま {Peach_Markup_Element::setAttributes()} 
     * の引数として使用することで属性のコピーをすることも出来ます.
     * 
     * @return array すべての属性の配列. キーが属性名, 値が属性値.
     * ただし値の省略された属性の場合は属性値が NULL となる.
     */
    public function getAttributes()
    {
        return $this->attributes->asArray();
    }
}
