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
/** @package DT */
/**
 * Java の
 * {@link http://docs.oracle.com/javase/jp/7/api/java/text/SimpleDateFormat.html SimpleDateFormat}
 * と同じような使い勝手で, ユーザー定義の書式を扱うことができるクラスです.
 * 日付・時刻のパターンは {@link date() date()} の一部を採用しています.
 * 
 * - Y: 年 (4桁固定)
 * - m: 月 (2桁固定)
 * - n: 月 (1～2桁)
 * - d: 日 (2桁固定)
 * - j: 日 (1～2桁)
 * - H: 時 (2桁固定)
 * - G: 時 (1～2桁)
 * - i: 分 (2桁固定)
 * - s: 秒 (2桁固定)
 * 
 * 以下は, このクラス独自の拡張パターンです.
 * 
 * - f: 分 (1～2桁)
 * - b: 秒 (1～2桁)
 * 
 * パターンを繋げて記述する場合は (例: "Ymd_His" など)
 * 必ず固定長のパターンを使用してください.
 * 可変長 (n, j, G など) のパターンは常に最長一致でマッチングするため,
 * 繋げて記述した際にパースに失敗する可能性があります.
 * 
 * このクラスは, 曜日や月の文字列表記のためのパターンをサポートしません.
 * そのようなフォーマットが必要となった場合は, 独自の Peach_DT_Format クラスを定義する必要があります.
 * 
 * パースまたは書式化を行う際, 情報が足りない場合はデフォルト値が指定されます.
 * 年・月・日については現在の日付, 時・分・秒については 0 が適用されます.
 * 具体的には, 以下のような状況が相当します.
 * 
 * 1. パースする際, オブジェクトを構成するために必要な情報をパターン文字列が網羅していなかった.
 *    (例: "m/d" というパターンをパースした場合, 
 *   「年」の情報がパターン文字列に含まれていないため, 現在の年が適用されます)
 * 2. 時間オブジェクトを書式化する際に, パターン文字列に含まれるフィールドを
 *    そのオブジェクトが持っていなかった.
 *    (例: "Y/m/d H:i:s" というパターンで Peach_DT_Date オブジェクトを書式化した場合,
 *    Peach_DT_Date は時刻の情報を持たないため, 時刻部分は 00:00:00 となります.)
 * 
 * PHP の date() 関数の実装と同様に, 
 * バックスラッシュをつけることでパターン文字列が展開されるのを抑制することができます.
 * 
 * このクラスはイミュータブルです. 一つのオブジェクトを複数の箇所で使いまわすことが出来ます.
 * 
 * @package DT
 */
class Peach_DT_SimpleFormat implements Peach_DT_Format
{
    /**
     * parse または format に使うパターン文字列です.
     * @var string
     */
    private $format;
    
    /**
     * 曜日文字列の一覧を表す, 長さ 7 の配列です.
     * @var array
     */
    private $dayList;
    
    /**
     * Pattern オブジェクトの配列です.
     * キーが "Y", "n" などのパターン文字, 値がその文字に該当する
     * Pattern オブジェクトとなります.
     * 
     * @var array
     */
    private $patternList;
    
    /**
     * パターン文字列を分解した結果をあらわします.
     * 
     * @var array
     */
    private $context;

    /**
     * 指定されたパターン文字列で SimpleFormat を初期化します.
     * 
     * @param string $pattern パターン文字列
     * @param array  $dayList 曜日文字列の配列. デフォルトは array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat")
     */
    public function __construct($pattern, array $dayList = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"))
    {
        $format        = strval($pattern);
        $this->format  = $format;
        $this->dayList = $this->initDayList($dayList);
        $this->patternList = $this->initPatternList($this->dayList);
        $this->context = $this->createContext($format);
    }
    
    /**
     * 曜日文字列を初期化します.
     * もしも配列の長さが 7 より大きかった場合, 8 個目以降の要素は無視されます.
     * 
     * @param  array $dayList 引数
     * @return array          曜日文字列の配列
     * @throws \InvalidArgumentException 配列の長さが 7 未満であるか, または空文字列が含まれている場合
     */
    private function initDayList(array $dayList)
    {
        $values = array_slice(array_values($dayList), 0, 7);
        $count  = count($values);
        if ($count !== 7) {
            throw new InvalidArgumentException("Invalid array count({$count}). Expected: 7");
        }
        for ($i = 0; $i < 7; $i++) {
            $value = $values[$i];
            if (!strlen($value)) {
                throw new InvalidArgumentException("Daystring is empty at index {$i}");
            }
        }
        return $values;
    }
    
    /**
     * パターン文字の一覧を作成します.
     * @param  array $dayList 曜日文字列の配列
     * @return array          Pattern オブジェクトの配列
     */
    private function initPatternList(array $dayList)
    {
        $patternList = $this->getDefaultPatternList();
        $patternList["E"] = new Peach_DT_SimpleFormat_Raw($dayList);
        return $patternList;
    }
    
    /**
     * このオブジェクトのパターン文字列を返します.
     * @return string パターン文字列
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * 指定された文字列を解析し, Peach_DT_Date に変換します.
     * @param  string $format 解析対象の文字列
     * @return Peach_DT_Date 解析結果
     */
    public function parseDate($format)
    {
        $d = Peach_DT_Date::now();
        return $d->setAll($this->interpret($format));
    }

    /**
     * 指定された文字列を解析し, Peach_DT_Datetime に変換します.
     * @param  string $format 解析対象の文字列
     * @return Peach_DT_Datetime 解析結果
     */
    public function parseDatetime($format)
    {
        $d = Peach_DT_Date::now();
        return $d->toDatetime()->setAll($this->interpret($format));
    }

    /**
     * 指定された文字列を解析し, Peach_DT_Timestamp に変換します.
     * @param  string $format 解析対象の文字列
     * @return Peach_DT_Date 解析結果
     */
    public function parseTimestamp($format)
    {
        $d = Peach_DT_Date::now();
        return $d->toTimestamp()->setAll($this->interpret($format));
    }

    /**
     * 指定された Peach_DT_Date オブジェクトを書式化します.
     * @param  Peach_DT_Date $d 書式化対象の時間オブジェクト
     * @return string このフォーマットによる文字列表現
     */
    public function formatDate(Peach_DT_Date $d)
    {
        return $this->formatTimestamp($d->toTimestamp());
    }

    /**
     * 指定された Peach_DT_Datetime オブジェクトを書式化します.
     * @param  Peach_DT_Datetime $d 書式化対象の時間オブジェクト
     * @return string このフォーマットによる文字列表現
     */
    public function formatDatetime(Peach_DT_Datetime $d)
    {
        return $this->formatTimestamp($d->toTimestamp());
    }

    /**
     * 指定された Peach_DT_Timestamp オブジェクトを書式化します.
     * @param  Peach_DT_Timestamp $d 書式化対象の時間オブジェクト
     * @return string このフォーマットによる文字列表現
     */
    public function formatTimestamp(Peach_DT_Timestamp $d)
    {
        $patternList = $this->patternList;
        $result      = "";
        foreach ($this->context as $part) {
            $buf = array_key_exists($part, $patternList) ? $this->formatKey($d, $part) : stripslashes($part);
            $result .= $buf;
        }
        return $result;
    }

    /**
     * 正規表現のパターン一覧を返します.
     * キーが変換文字, 値がその文字に対応する Pattern オブジェクトとなります.
     * 
     * @return array
     * @codeCoverageIgnore
     */
    private function getDefaultPatternList()
    {
        static $patterns = null;
        if (!isset($patterns)) {
            $fixed4   = "\\d{4}";
            $fixed2   = "\\d{2}";
            $var2     = "[1-5][0-9]|[0-9]";
            $varM     = "1[0-2]|[1-9]";
            $varD     = "3[0-1]|[1-2][0-9]|[0-9]";
            $varH     = "2[0-4]|1[0-9]|[0-9]";
            $patterns = array(
                "Y" => new Peach_DT_SimpleFormat_Numbers("year",   $fixed4),
                "m" => new Peach_DT_SimpleFormat_Numbers("month",  $fixed2),
                "n" => new Peach_DT_SimpleFormat_Numbers("month",  $varM),
                "d" => new Peach_DT_SimpleFormat_Numbers("date",   $fixed2),
                "j" => new Peach_DT_SimpleFormat_Numbers("date",   $varD),
                "H" => new Peach_DT_SimpleFormat_Numbers("hour",   $fixed2),
                "G" => new Peach_DT_SimpleFormat_Numbers("hour",   $varH),
                "i" => new Peach_DT_SimpleFormat_Numbers("minute", $fixed2),
                "f" => new Peach_DT_SimpleFormat_Numbers("minute", $var2),
                "s" => new Peach_DT_SimpleFormat_Numbers("second", $fixed2),
                "b" => new Peach_DT_SimpleFormat_Numbers("second", $var2),
            );
        }
        return $patterns;
    }

    /**
     * 指定された文字列に相当する Pattern オブジェクトを返します.
     * 
     * @param  string $part
     * @return Pattern
     * @codeCoverageIgnore
     */
    private function getPatternByPart($part)
    {
        $patterns = $this->patternList;
        return array_key_exists($part, $patterns) ? $patterns[$part] : new Peach_DT_SimpleFormat_Raw(array(stripslashes($part)));
    }
    
    /**
     * 指定されたパターン文字を, 対応するフィールドの値に変換します.
     * 
     * @param  Peach_DT_Time $d   変換対象の時間オブジェクト
     * @param  string        $key パターン文字 ("Y", "m", "d" など)
     * @return int                変換結果
     * @throws Exception          不正なパターン文字が指定された場合
     */
    private function formatKey(Peach_DT_Time $d, $key)
    {
        $year  = $d->get("year");
        $month = $d->get("month");
        $date  = $d->get("date");
        $hour  = $d->get("hour");
        $min   = $d->get("minute");
        $sec   = $d->get("second");
        
        switch ($key) {
            case "Y":
                return str_pad($year,  4, "0", STR_PAD_LEFT);
            case "m":
                return str_pad($month, 2, "0", STR_PAD_LEFT);
            case "n":
                return $month;
            case "d":
                return str_pad($date,  2, "0", STR_PAD_LEFT);
            case "j":
                return $date;
            case "H":
                return str_pad($hour,  2, "0", STR_PAD_LEFT);
            case "G":
                return $hour;
            case "i":
                return str_pad($min,   2, "0", STR_PAD_LEFT);
            case "f":
                return $min;
            case "s":
                return str_pad($sec,   2, "0", STR_PAD_LEFT);
            case "b":
                return $sec;
            case "E":
                return $this->dayList[$d->getDay()];
        }
        
        // @codeCoverageIgnoreStart
        throw new Exception("Illegal pattern: " . $key);
        // @codeCoverageIgnoreEnd
    }
    
    /**
     * 指定されたパターン文字列 (例えば "Y/m/d" など) を構文解析します.
     * 
     * @param  string $format パターン文字列
     * @return array          解析結果
     */
    private function createContext($format)
    {
        $patternList = $this->patternList;
        $result      = array();
        $current     = "";
        $escaped     = false;
        for ($i = 0, $length = strlen($format); $i < $length; $i ++) {
            $chr = substr($format, $i, 1);
            if ($escaped) {
                $current .= $chr;
                $escaped = false;
            } else if ($chr === "\\") {
                $current .= $chr;
                $escaped = true;
            } else if (array_key_exists($chr, $patternList)) {
                if (strlen($current)) {
                    $result[] = $current;
                    $current = "";
                }
                $result[] = $chr;
            } else {
                $current .= $chr;
            }
        }
        if (strlen($current)) {
            $result[] = $current;
        }
        return $result;
    }

    /**
     * 指定されたテキストを構文解析します.
     * 
     * @param  string $text 解析対象の文字列
     * @return array        構文解析した結果
     */
    private function interpret($text)
    {
        $input       = $text;
        $result      = new Peach_Util_ArrayMap();
        $matched     = null;
        foreach ($this->context as $part) {
            $pattern = $this->getPatternByPart($part);
            $matched = $pattern->match($input);
            if ($matched === null) {
                $this->throwFormatException($input, $this->format);
            }
            $pattern->apply($result, $matched);
            $input = substr($input, strlen($matched));
        }
        return $result;
    }

    /**
     * 文字列を時間オブジェクトに変換する際に, 不正な文字列が指定された場合に例外をスローします.
     * 
     * @param  string $format   指定された文字列
     * @param  string $expected 想定されるパターン文字列
     * @throws InvalidArgumentException
     */
    private function throwFormatException($format, $expected)
    {
        throw new InvalidArgumentException("Illegal format({$format}). Expected: {$expected}");
    }
}
