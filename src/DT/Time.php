<?php
/** @package DT */
/** */
require_once(dirname(__FILE__) . "/../Util/load.php");

/**
 * ある特定の日付または時刻を表すインタフェースです.
 * このインタフェースを実装したオブジェクトのことを「時間オブジェクト」と呼びます.
 * 時間オブジェクトには以下の 3 種類があります.
 * 
 * - DATE : 年・月・日のフィールドを持ちます
 * - DATETIME : 年・月・日・時・分のフィールドを持ちます
 * - TIMESTAMP : 年・月・日・時・分・秒のフィールドを持ちます
 * 
 * とある時間オブジェクトについて, そのオブジェクトの型が何かを調べるには
 * {@link DT_Time::getType() getType()} メソッドを使用してください.
 * 
 * 時間オブジェクトを操作するための各種メソッド (get, add, set, setAll など) 
 * は文字列型の引数を指定して呼び出す仕様となっていますが,
 * 具体的には以下の値を指定してください. (大文字・小文字を問いません)
 * 
 * - "year" : 年
 * - "month" : 月
 * - "date" : 日
 * - "hour" : 時
 * - "minute" : 分
 * - "second" : 秒
 * 
 * このクラスはイミュータブルであるため, 各種フィールド操作メソッド (add, set, setAll) 
 * は新しい時間オブジェクトを返すことに注意してください.
 * 
 * このクラスは {@link Util_Comparable} を実装しているため, {@link Util_Arrays::sort()} でソートすることが出来ます.
 * 
 * @package DT
 */
interface DT_Time extends Util_Comparable {
    /**
     * このオブジェクトが年・月・日のフィールドをサポートしていることを示します.
     */
    const TYPE_DATE      = 129;
    
    /**
     * このオブジェクトが年・月・日・時・分のフィールドをサポートしていることを示します.
     */
    const TYPE_DATETIME  = 130;
    
    /**
     * このオブジェクトが年・月・日・時・分・秒のフィールドをサポートしていることを示します.
     */
    const TYPE_TIMESTAMP = 131;
    
    /**
     * 日曜日をあらわす定数です.
     * @var int
     */
    const SUNDAY    = 0;
    
    /**
     * 月曜日をあらわす定数です
     * @var int
     */
    const MONDAY    = 1;
    
    /**
     * 火曜日をあらわす定数です
     * @var int
     */
    const TUESDAY   = 2;
    
    /**
     * 水曜日をあらわす定数です
     * @var int
     */
    const WEDNESDAY = 3;
    
    /**
     * 木曜日をあらわす定数です
     * @var int
     */
    const THURSDAY  = 4;
    
    /**
     * 金曜日をあらわす定数です
     * @var int
     */
    const FRIDAY    = 5;
    
    /**
     * 土曜日をあらわす定数です
     * @var int
     */
    const SATURDAY  = 6;
    
    /**
     * このオブジェクトの型を返します.
     * 返り値の数値は, 上位の (より多くのフィールドをサポートしている) 型ほど大きくなります.
     * そのため, 2 つの時間オブジェクトの getType の返り値を比較することにより, 
     * どちらのオブジェクトのほうがサポートしているフィールドが多いか調べることができます.
     * 
     * @return int オブジェクトの型
     * 
     * @see DT_Time::TYPE_DATE
     * @see DT_Time::TYPE_DATETIME
     * @see DT_Time::TYPE_TIMESTAMP
     */
    public function getType();
    
    /**
     * 指定されたフィールドの値を取得します.
     * @param  string $field フィールド名
     * @return int           対象フィールドの値. ただしフィールド名が不正な場合は NULL
     */
    public function get($field);
    
    /**
     * この時間オブジェクトの指定されたフィールドを上書きします.
     * 
     * @param  string $field フィールド名
     * @param  int    $value 設定する値
     * @return DT_Time 設定後の時間オブジェクト
     */
    public function set($field, $value);
    
    /**
     * この時間オブジェクトの複数のフィールドを一度に上書きします.
     * 引数には, 
     * <code>array("year" => 2010, "month" => 8, "date" => 31)</code>
     * などの配列か, または同様の Map オブジェクトを指定してください.
     * 
     * @param  Util_Map|array フィールドと値の一覧
     * @return DT_Time        設定後の時間オブジェクト
     */
    public function setAll($subject);
    
    /**
     * 引数のフィールドを, $amount だけ増加 (負の場合は減少) させます.
     * @param  string  対象のフィールド
     * @param  int     加算する量. マイナスの場合は過去方向に移動する.
     * @return DT_Time 設定後の時間オブジェクト
     */
    public function add($field, $amount);
    
    /**
     * 指定されたフォーマットを使ってこの時間オブジェクトを書式化します.
     * フォーマットを指定しない場合はデフォルトの方法 (SQL などで使われる慣用表現) で書式化を行ないます.
     * @param  DT_Format $format
     * @return string            このオブジェクトの書式化.
     *                           引数を指定しない場合は "YYYY-MM-DD" あるいは "YYYY-MM-DD hh:mm:ss" などの文字列
     */
    public function format(DT_Format $format = NULL);
    
    /**
     * 指定されたオブジェクトとこのオブジェクトを比較します.
     * 二つのオブジェクトが等しいと判断された場合に TRUE を返します.
     *
     * @param  mixed   $obj 比較対象のオブジェクト
     * @return boolean      二つのオブジェクトが等しい場合に TRUE, それ以外は FALSE
     */
    public function equals($obj);
    
    /**
     * 指定された時間とこの時間を比較します.
     *
     * もしもこのオブジェクトが持つ時間フィールドすべてが
     * 引数のオブジェクトの時間フィールドと一致した場合, 
     * より多くの時間フィールドを持つほうが「後」となります.
     * 
     * 例: 2012-05-21 (DT_Date) < 2012-05-21T00:00 (DT_Datetime) < 2012-05-21T00:00:00 (DT_Timestamp)
     *
     * @param  DT_Time $time 比較対象の時間
     * @return boolean         この時間のほうが過去である場合は TRUE, それ以外は FALSE
     */
    public function before(DT_Time $time);
    
        /**
     * 指定された時間とこの時間を比較します.
     *
     * もしもこのオブジェクトが持つ時間フィールドすべてが
     * 引数のオブジェクトの時間フィールドと一致した場合, 
     * より多くの時間フィールドを持つほうが「後」となります.
     * 
     * 例: 2012-05-21 (DT_Date) < 2012-05-21T00:00 (DT_Datetime) < 2012-05-21T00:00:00 (DT_Timestamp)
     *
     * @param  DT_Time $time 比較対象の時間
     * @return boolean   この時間のほうが未来である場合は TRUE, それ以外は FALSE
     */
    public function after(DT_Time $time);
    
    /**
     * この年がうるう年かどうかを判定します.
     *
     * うるう年の判別ルールは以下の通りです.
     * - 4 で割り切れるはうるう年である
     * - ただし、100 で割り切れる年はうるう年ではない
     * - ただし、400 で割り切れる年はうるう年である
     * 
     * @return bool うるう年である場合に TRUE, それ以外は FALSE
     */
    public function isLeapYear();
    
    /**
     * この日付の曜日を返します. 返される値は 0 から 6 までの整数で, 0 が日曜, 6 が土曜をあらわします.
     * それぞれの整数は, このクラスで定義されている各定数に対応しています.
     * 
     * @return int 曜日 (0 以上 6 以下の整数)
     * 
     * @see    DT_Time::SUNDAY
     * @see    DT_Time::MONDAY
     * @see    DT_Time::TUESDAY
     * @see    DT_Time::WEDNESDAY
     * @see    DT_Time::THURSDAY
     * @see    DT_Time::FRIDAY
     * @see    DT_Time::SATURDAY
     */
    public function getDay();
    
    /**
     * この月の日数を返します.
     * @return int この月の日数. すなわち, 28 から 31 までの整数.
     */
    public function getDateCount();
    
    /**
     * このオブジェクトを DATE 型にキャストします.
     * このメソッドの返り値は以下の性質を持ちます.
     * 
     * - 年・月・日のフィールドをサポートします.
     * - {@link DT_Time::getType() getType()} を実行した場合 {@link DT_Time::TYPE_DATE} を返します.
     * 
     * デフォルトの実装では {@link DT_Date} 型にキャストします.
     *
     * @return DT_Time DATE 型の時間オブジェクト
     */
    public function toDate();
    
    /**
     * このオブジェクトを DATETIME 型にキャストします.
     * このメソッドの返り値は以下の性質を持ちます.
     * 
     * - 年・月・日・時・分のフィールドをサポートします.
     * - {@link DT_Time::getType() getType()} を実行した場合 {@link DT_Time::TYPE_DATETIME} を返します.
     * 
     * デフォルトの実装では {@link DT_Datetime} オブジェクトを返します.
     * 
     * @return DT_Time DATETIME 型の時間オブジェクト
     */
    public function toDatetime();
    
    /**
     * このオブジェクトを TIMESTAMP 型にキャストします.
     * このメソッドの返り値は以下の性質を持ちます.
     * 
     * - 年・月・日・時・分・秒のフィールドをサポートします.
     * - {@link DT_Time::getType() getType()} を実行した場合 {@link DT_Time::TYPE_TIMESTAMP} を返します.
     * 
     * デフォルトの実装では {@link DT_Timestamp} オブジェクトを返します.
     * 
     * @return DT_Time TIMESTAMP 型の時間オブジェクト
     */
    public function toTimestamp();
    
    /**
     * この時間の時刻 (時・分・秒) 部分を書式化します.
     * 返される文字列はタイプによって以下の通りとなります.
     * 
     * - DATE : 空文字列
     * - DATETIME : "hh:mm" 
     * - TIMESTAMP : "hh:mm:ss"
     * 
     * @return string 時刻部分の文字列. このオブジェクトが時刻をサポートしない場合は空文字列.
     */
    public function formatTime();
}
?>