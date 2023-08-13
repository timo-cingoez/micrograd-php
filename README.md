
# micrograd-php

A basic implementation of the Value class in the Autograd engine of https://github.com/karpathy/micrograd but in PHP. (Yes, really.)

### Example usage

It correctly runs the basic example of operations and backward pass as illustrated in karpathy/micrograd.

```PHP
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
```

### License

MIT
