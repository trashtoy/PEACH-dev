<?php
/** @package Util */
/**
 * 二つの値が等価かどうかを調べるためのインタフェースです.
 * {@link Util_HashMap} などでキーの等価性をチェックするために使用されます.
 * 
 * @todo いわゆる "Latitude Zero" と区別するため, 違うインタフェース名にしたい
 * @package Util
 */
interface Util_Equator {
    /**
     * 引数 $var1 と $var2 が等しいと判断した場合に TRUE を返します.
     * 
     * @param  mixed $var1
     * @param  mixed $var2
     * @return bool  引数の $var1, $var2 が等しい場合に TRUE を返します.
     */
    public function equate($var1, $var2);
    
    /**
     * 指定された値のハッシュ値を計算します.
     * もしもこのオブジェクトの equate($var1, $var2) が TRUE を返す場合, 
     * hashCode($var1) と hashCode($var2) が必ず同じ値を返すようにしなければなりません.
     * 
     * この制約条件は Java の
     * {@link http://java.sun.com/javase/ja/6/docs/ja/api/java/lang/Object.html#equals(java.lang.Object) java.lang.Object#equals()}, 
     * {@link http://java.sun.com/javase/ja/6/docs/ja/api/java/lang/Object.html#hashCode() java.lang.Object#hashCode()}
     * で定義されている規約に相当します.
     * 
     * @param  mixed $var
     * @return int
     */
    public function hashCode($var);
}
?>