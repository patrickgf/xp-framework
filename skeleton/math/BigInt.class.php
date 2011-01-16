<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('math.BigNum');

  /**
   * (Insert class' description here)
   *
   * @ext      xp://math.BigNum
   */
  class BigInt extends BigNum {
    
    /**
     * &
     *
     * @param   var other
     * @return  math.BigNum
     */
    public function bitwiseAnd($other) {
      $a= self::bytesOf($this->num);
      $b= self::bytesOf($other instanceof self ? $other->num : $other);
      $l= max(strlen($a), strlen($b));
      return self::fromBytes(str_pad($a, $l, "\0", STR_PAD_LEFT) & str_pad($b, $l, "\0", STR_PAD_LEFT));
    }

    /**
     * |
     *
     * @param   var other
     * @return  math.BigNum
     */
    public function bitwiseOr($other) {
      $a= self::bytesOf($this->num);
      $b= self::bytesOf($other instanceof self ? $other->num : $other);
      $l= max(strlen($a), strlen($b));
      return self::fromBytes(str_pad($a, $l, "\0", STR_PAD_LEFT) | str_pad($b, $l, "\0", STR_PAD_LEFT));
    }

    /**
     * ^
     *
     * @param   var other
     * @return  math.BigNum
     */
    public function bitwiseXor($other) {
      $a= self::bytesOf($this->num);
      $b= self::bytesOf($other instanceof self ? $other->num : $other);
      $l= max(strlen($a), strlen($b));
      return self::fromBytes(str_pad($a, $l, "\0", STR_PAD_LEFT) ^ str_pad($b, $l, "\0", STR_PAD_LEFT));
    }

    /**
     * >>
     *
     * @param   var shift
     * @return  math.BigNum
     */
    public function shiftRight($shift) {
      return new $this(bcdiv($this->num, bcpow(2, $shift instanceof self ? $shift->num : $shift), 0));
    }
    
    /**
     * <<
     *
     * @param   var shift
     * @return  math.BigNum
     */
    public function shiftLeft($shift) {
      return new $this(bcmul($this->num, bcpow(2, $shift instanceof self ? $shift->num : $shift), 0));
    }
    
    /**
     * Creates a bignum from a sequence of bytes
     *
     * @see     xp://math.BigNum#toBytes
     * @param   string bytes
     * @return  math.BigNum
     */
    protected static function fromBytes($bytes) {
      $len= strlen($bytes);
      $len+= (3 * $len) % 4;
      $bytes= str_pad($bytes, $len, "\0", STR_PAD_LEFT);
      $self= new self(0);
      for ($i= 0; $i < $len; $i+= 4) {
        $self->num= bcadd(bcmul($self->num, '4294967296'), 0x1000000 * ord($bytes{$i}) + current(unpack('N', "\0".substr($bytes, $i+ 1, 3))));
      }      
      return $self;
    }
    
    /**
     * Creates sequence of bytes from a bignum
     *
     * @see     xp://math.BigNum#fromBytes
     * @return  string
     */
    protected static function bytesOf($n) {
      $value= '';
      while (bccomp($n, 0) > 0) {
        $value= substr(pack('N', bcmod($n, 0x1000000)), 1).$value;
        $n= bcdiv($n, 0x1000000);
      }
      return ltrim($value, "\0");    
    }
    
    /**
     * String cast overloading
     *
     * @return  string
     */
    public function __toString() {
      return substr($this->num, 0, strcspn($this->num, '.'));
    }

    /**
     * Returns an byte representing this big integer
     *
     * @return  int
     */
    public function byteValue() {
      return $this->bitwiseAnd(0xFF)->intValue();
    }
  }
?>