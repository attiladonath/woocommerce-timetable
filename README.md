# WooCommerce Timetable
A minimalist approach to handle opening hours with WooCommerce.

The plugin shows a notice on the cart and checkout page and prevents checkout
when the shop is closed.

## Using the plugin

Install and activate the plugin as usual.
After that you can access the settings on the
`wp-admin/admin.php?page=woocommerce-timetable-admin` admin page.

Here you can either manually close the shop by checking the "Closed" checkbox,
or define a timetable that represents the opening hours.

## Timetable format

The basic format is:
```
[date] [time_from]-[time_to] [time_from]-[time_to] ...
...
```

`[date]` can be:
* The name of a day, e.g. `Monday`.
* A full date, e.g. `2016-10-10`.
* A date with wildcards, e.g. `2016-*-01`.
  Every part can be a wildcard, `*-*-*` is perfectly valid.

`[time_from] and [time_to]` are times in `H:i` format, e.g. `10:30`.

The time intervals are closed from left side and open from right side:
`from` <= `now` < `to`

If you define multiple overriding dates, the lower rule overrides the upper.

Say you have a timetable like that:
```
Monday     10:00-18:00
2016-10-10 12:00-18:00
```
In that case, the shop is closed on 2016-10-10 at 11:00, but it is open on
every other Mondays at 11:00.

## Running tests

```
phpunit tests
```
