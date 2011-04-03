<?php
/**
* Fraction class
* Author: Ferry Firmansjah (firmanf@bigfoot.com)
* Version: 0.8 (getting there, but still not perfect yet)
* Last update: 2001/05/28
* Description:
*   A class that represents a Fraction number.
*   The class can perform the more common mathematical
*   operations: addition, subtraction, multiplication,
*   division.  The only caveat is that the other
*   argument also has to be a Fraction.
*
*   The constructor is able to accept almost any variable
*   data type: string, double, integer, even another Fraction.
*   The class is also able to reduce the Fraction to
*   (practically) the lowest common denominator (using the
*   reduce() method call) using Euclid's Algorithm
*   (www.mcn.net/~jimloy/euclids.html).
*
* Changes:
*   For 0.8:
*   Pretty major reorganization of the classes: we now
*   use a factory to get the Fraction.
*   The advantages are:
*   - all Fraction has a reference to the factory that
*     created it.
*   - the factory also provides methods to manipulate
*     the Fraction class itself.
*
*   Modification to fraction/Fraction class:
*   - class name changed to Fraction; 'fraction' will still
*     work.
*   - Fraction class constructor now accepts an optional
*     second parameter which is a reference to the fraction
*     factory/tool.
*   - Fraction class now correctly prints 0 if the fraction's
*     value is 0.
*   - a lot of the functionality has been moved to the
*     FractionTool class -- which should make the Fraction
*     class leaner.
*   - Fraction takes advantage of a singleton FractionTool
*     instance.
*
*   Modification to fractionTool/FractionTool class:
*   - class name changed to FractionTool; 'fractionTool' will still
*     work.
*   - reduce corrected to work with negative numbers.
*     Previously, this would cause an infinite loop.
*   - upon instantiation, unserialize self to the global
*     constant variable 'FractionTool_instance'.  If
*     fraction class needs this, then the FractionTool
*     can be retrieved by deserializing the 'FractionTool_instance'
*     constant variable.
*
*
*   For 0.7:
*   ========
*   pack() is now replaced by reduce().
*   toString() now does not automatically
*     reduce().
*
* To do:
*   (1) Add other operations if needed.  I don't know
*       how much interest there is for the more complex
*       operations, but it might be added in the future.
*       I just don't want the class to get too bloated.
*
*/
class Fraction {
  var $num = 1, $denom = 1;
  var $classname = "Fraction";
  var $tool = null;

  /**
   * Creates a Fraction.  This constructor accepts
   * a string, double, integer, or an array of
   * one, two or three elements.
   * e.g. $val1 = new Fraction("5 1/3");
   *      $val1 = new Fraction(1.25);
   *      $val1 = new Fraction(20);
   *      $val1 = new Fraction(array (5, 1, 3));
   */
  function Fraction ($val, $tool=null) {
    $this->tool = $tool;
    if (is_string($val)) {
      if (ereg("^(-?[0-9]+)$", $val, $regs)) {
        $this->create(0, $regs[1],1);
      } elseif (ereg("^(-?[0-9]+)[ ]*/[ ]*([0-9]+)$",$val,$regs)) {
        $this->create(0, $regs[1], $regs[2]);
      } elseif (ereg("^(-?[0-9]+)[ ]+([0-9]+)[ ]*/[ ]*([0-9]+)$",$val,$regs)){
        $this->create($regs[1], $regs[2], $regs[3]);
      }
    } elseif (is_double($val)) {
      $len = strlen($val);
      $pos = strpos($val, ".");
      $this->create(0, ((double)$val) * pow (10, ($len - $pos - 1))
        , pow (10, ($len - $pos - 1)));
    } elseif (is_int($val)) {
      $this->create(0, $val,1);
    } elseif (is_array($val)) {
      if (sizeof($val) == 1) { $this->create(0, $val[0], 1); }
      elseif (sizeof($val)==2) { $this->create(0, $val[0],$val[1]); }
      elseif (sizeof($val)>=3) {
        $this->create ($val[0], $val[1], $val[2]);
      }
    } elseif (is_bool($val)) {
      $this->create(0, ($val?$val:0), 1);
    } else {
      // don't know what to do... just return null
      return $this->create (0, 0, 1);
    }
  }

  /** Inner method that actually creates the Fraction.
   * This should not be called directly from outside of this class.
   */
  function create($whole, $num, $denom) {
    if ($denom == 0) { $denom = 1; }
    // negative?
    if ((($whole < 0) xor ($denom < 0)) xor ($num < 0)) {
      // yes make the numerator negative
      $this->num = -1 * (abs($whole) * abs($denom) + abs($num));
    } else {
      $this->num = (abs($whole) * abs($denom) + abs($num));
    }
    // denom always positive
    $this->denom = abs($denom);
  }

  /** Subtract another Fraction/integer from this Fraction */
  function subtract ($other) {
    $this->getTool();
    return ($this->tool->subtract(&$this, $other));
  }

  /**
   * Adds another Fraction/integer to this Fraction.
   */
  function add ($other) {
    $this->getTool();
    return ($this->tool->add(&$this, $other));
  }

  /** Multiple this Fraction by another Fraction or whole number */
  function multiply ($other) {
    $this->getTool();
    return ($this->tool->multiply(&$this, $other));
  }

  /** Divide this Fraction by another Fraction or whole number */
  function divide ($other) {
    $this->getTool();
    return ($this->tool->divide(&$this, $other));
  }


  /** Whether the object is a Fraction or not */
  function isFraction ($other) {
    $this->getTool();
    return ($this->tool->isFraction($other));
  }

  /** Reduce the Fraction */
  function reduce () {
    $this->getTool();
    $this->tool->reduce (&$this);
  }

  /** Returns a string representation of the Fraction. */
  function toString() {
    if (abs($this->num) >= abs($this->denom)) {
      if ($this->denom == 1) {
        return $this->num;
      }

      if ($this->isNegative()) {
        $whole = ceil ($this->num / $this->denom);
      } else {
        $whole = floor ($this->num / $this->denom);
      }
      $num = abs($this->num % $this->denom);
    } else {
      $num = $this->num;
    }
    return ((empty($whole)?(empty($num)?"0":""):$whole)
      . (empty($num)? "" : " " . $num ."/". $this->denom));
  }

  /** Returns a double representation of the Fraction **/
  function toDouble() {
    return (double) $this->num / $this->denom;
  }

  /**
   * Whether this Fraction is equal to another Fraction.
   * Returns true or half.
   */
  function equals ($other) {
    $this->getTool();
    if ($this->tool->isFraction($other)) {
      return ($this->tool->areEqual ($this, $other));
    } elseif ($this->tool->canBeFraction($other)) {
      $tmp = $this->tool->createFraction($other);
      return ($this->equals($tmp));
    }
    return false;
  }

  /**
   * Is this Fraction negative?
   * Returns true or false.
   */
  function isNegative () {
    return (($this->num < 0) xor ($this->denom < 0));
  }

  function getTool() {
    if ($this->tool == null) {
      if (defined ("FractionTool_instance")) {
        $this->tool = unserialize (FractionTool_instance);
      } else {
        $this->tool = new FractionTool();
        define ("FractionTool_instance", serialize($this->tool));
      }
    }
    return $this->tool;
  }
}

/**
* Fraction related tool.  Used by Fraction, but
* this class does not require Fraction.
*/
class FractionTool {
  var $classname = "FractionTool";

  /* Constructor */
  function FractionTool(){
    // create an instance, and store it in a global instance variable
    if (! defined ("FractionTool_instance")) {
      define ("FractionTool_instance", serialize($this));
    }
  }

  function createFraction ($val) {
    return (new Fraction ($val, &$this));
  }

  /**
   * Find the greatest common divisor (gcd)
   * using Euclid's Algorithm. (read it from
   * http://www.mcn.net/~jimloy/euclids.html)
   * Takes in 2 values (numerator and denominator)
   * and returns the greatest common divisor.
   * Ideally, the first parameter is the larger
   * number of the two, but it's not necessary.
   */
  function gcd ($a, $b) {
    if ($b < 0 || $a < 0) {
      return $this->gcd(abs($a), abs($b));
    }
    if ($b > $a) {
      return $this->gcd($b, $a);
    }
    $rem = $a % $b;
    return (($rem == 0)?$b:$this->gcd($b,$rem));
  }

  /**
   * Lowest Common Denominator (lcd) or
   * Lowest Common Multiple (lcm)
   */
  function lcd ($a, $b) {
    return (($a * $b) / $this->gcd($a, $b));
  }

  /** Lowest Common Multiple (lcm),
   * another name for Lowest Common Denominator (lcd)
   */
  function lcm($a, $b) {
    return $this->lcd($a, $b);
  }

  /** Subtract the part (Fraction or whole number) from the whole (Fraction) */
  function subtract ($whole, $part) {
    if ($this->isFraction($whole)
        && ($this->isFraction($part) || is_int($part))) {
      if (is_int($part)) {
        $whole->num -= $part * $whole->denom;
      } else {
        $lcd = $this->lcd ($whole->denom, $part->denom);
        $whole->num *= ($lcd / $whole->denom);
        $whole->denom *= ($lcd / $whole->denom);
        $whole->num -= ($part->num * ($lcd / $part->denom));
        $whole->reduce();
      }
    }
    return $whole;
  }

  /**
   * Adds a Fraction to another Fraction or whole number.
   */
  function add ($other, $another) {
    if ($this->isFraction($other)
        && ($this->isFraction($another) || is_int($another))) {
      if (is_int($another)) {
        $other->num += $another * $other->denom;
      } else {
        $lcd = $this->lcd ($other->denom, $another->denom);
        $other->num *= ($lcd / $other->denom);
        $other->denom *= ($lcd / $other->denom);
        $other->num += ($another->num * ($lcd / $another->denom));
        $other->reduce();
      }
    }
    return $other;
  }

  /** Multiple a Fraction by another Fraction or whole number */
  function multiply ($other, $another) {
    if ($this->isFraction($other)
        && ($this->isFraction($another) || is_int($another))) {
      if (is_int($another)) {
        $other->num *= $another;
      } else {
        $other->num *= $another->num;
        $other->denom *= $another->denom;

        // reduce at the end as we usually get big numbers here
        $other->reduce();
      }
    }
    return $other;
  }

  /** Divide the dividend Fraction by the divisor (Fraction or whole number) */
  function divide ($dividend, $divisor) {
    if ($this->isFraction($dividend)
        && ($this->isFraction($divisor) || is_int($divisor))) {
      if (is_int($divisor)) {
        $dividend->denom *= $divisor;
      } else {
        $dividend->num *= $divisor->denom;
        $dividend->denom *= $divisor->num;
      }
    }
    $dividend->reduce();
    return $dividend;
  }




  /**
   * Whether 2 Fractions are equal.
   * Returns true or false.
   */
  function areEqual ($other, $another) {
    if ($this->isFraction($other) && $this->isFraction($another)) {
      $other->reduce();
      $another->reduce();
      return ($other->toString() == $another->toString());
    } else {
      return false;
    }
  }

  /** Reduce the Fraction */
  function reduce (&$obj) {
    if ($this->isFraction($obj)) {
      $gcd = $this->gcd($obj->num, $obj->denom);
      $obj->num /= $gcd;
      $obj->denom /= $gcd;
    }
    return $obj;
  }

  /**
   * Whether the object is a Fraction.
   */
  function isFraction ($obj) {
    return (is_object($obj)
        && isset($obj->classname)
        && $obj->classname == "Fraction");
  }

  /**
   * Whether the variable can be turned into a Fraction.
   */
  function canBeFraction ($obj) {
    return (is_string($obj)
        || is_double($obj)
        || is_integer($obj)
        || is_array($obj));
  }
}

?>
