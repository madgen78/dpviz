# Contributors

## By order of appearance

* Alexandre Salomé
* George Petsagourakis
* Aurélien Fredouelle
* Olivier Dolbeau
* Clemens Tolboom
* Oskar Stark

## Command used to generate:

```
git log --reverse --format="%aN" \
| sed "s/alexandresalome/Alexandre Salomé/g" \
| perl -ne 'if (!defined $x{$_}) { print $_; $x{$_} = 1; }'
```
