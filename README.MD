# Kohana Database

This is my version of the Kohana Database library for Kohana v3.0.

## Changes:

These are a brief 

* Added DDL methods (CREATE, ALTER, DROP & TRUNCATE).
* Created normalised objects for databases, tables and columns.
* New database instances are created using Database::factory(). (Which removed dependencies on the Kohana config library).
* Interfaced database drivers eg. MySQL.
* Changed class names to ones that made more sense, eg. Database_Query to Database_Result_Query.
* Added introspection. Individual drivers will be required to target database specific datatypes.

## Useage Examples:

Here are some basic examples of how to use the library.

### Creating a new table.

Soon to come!

### Altering a table / column.

Soon to come!

### Dropping a table / column.

Soon to come!