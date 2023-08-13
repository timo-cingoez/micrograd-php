<?php

class Value {
    public $data;
    public $_backward;
    public $grad;
    public $_prev;
    public $_op;
    
    public function __construct($data, $_children = [], $_op = '') {
        $this->data = $data;
        $this->grad = 0;
        $this->_backward = function () {};
        $this->_prev = $_children;
        $this->_op = $_op;
    }
    
    public function add($other) {
        $other = $other instanceof Value ? $other : new self($other);
        $out = new self($this->data + $other->data, [$this, $other], '+');
        $out->_backward = function () use ($out, $other) {
            $this->grad += $out->grad;
            $other->grad += $out->grad;
        };
        return $out;
    }
    
    public function mul($other) {
        $other = $other instanceof Value ? $other : new self($other);
        $out = new self($this->data * $other->data, [$this, $other], '*');
        $out->_backward = function () use ($out, $other) {
            $this->grad += $other->data * $out->grad;
            $other->grad += $this->data * $out->grad;
        };
        return $out;
    }
    
    public function pow($other) {
        if (!is_float($other) && !is_int($other)) {
            exit('only int/float powers supported');
        }
        $out = new self($this->data ** $other, [$this], "**{$other}");
        $out->_backward = function () use ($out, $other) {
            $this->grad += ($other * $this->data ** ($other - 1)) * $out->grad;
        };
        return $out;
    }
    
    public function relu() {
        $out = new self(max($this->data, 0), [$this], 'ReLU');
        $out->_backward = function () use ($out) {
            $this->grad += ($out->data > 0) * $out->grad;
        };
        return $out;
    }
    
    public function backward() {
        $topo = $visited = array();
        $builder = function ($v) use (&$visited, &$topo, &$builder) {
            if (!in_array($v, $visited)) {
                $visited[] = $v;
                foreach ($v->_prev as $child) {
                    $builder($child);
                }
                $topo[] = $v;
            }
        };
        $builder($this);
        
        $this->grad = 1;
        foreach (array_reverse($topo) as $v) {
            $v->_backward->__invoke();;
        }
    }
    
    public function sub($other) {
        return $this->add($other->neg());
    }
    
    public function div($other) {
        $other = $other instanceof Value ? $other : new self($other);
        return $this->mul($other->pow(-1));
    }
    
    public function rdiv($other) {
        $other = $other instanceof Value ? $other : new self($other);
        return $other->mul($this->pow(-1));
    }
    
    public function neg() {
        return $this->mul(-1);
    }
    
    public function out() {
        echo "Value(data={$this->data}, grad={$this->grad})\n";
    }
}

$a = new Value(-4.0);
$b = new Value(2.0);
$c = $a->add($b);
$d = $a->mul($b)->add($b->pow(3));
$c = $c->add($c->add(1));
$c = $c->add($c->add(1))->add($a->neg());
$d = $d->add($d->mul(2))->add($b->add($a)->relu());
$d = $d->add($d->mul(3)->add($b->sub($a)->relu()));
$e = $c->sub($d);
$f = $e->pow(2);
$g = $f->mul(0.5);
$g = $g->add((new Value(10.0))->div($f));
echo number_format($g->data, 4)."\n"; // prints 24.7041
$g->backward();
echo number_format($a->grad, 4)."\n"; // prints 138.8338
echo number_format($b->grad, 4)."\n"; // prints 645.5773
