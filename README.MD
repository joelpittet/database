# Kohana Database

This is my version of the Kohana Database library for Kohana v3.0.

Create by: Olly
Credit to: Kohana Development Team

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

### Setting up a new database instance.
Database instances are managed slightly differently then before. This time, you create new database instances using
the factory method:

	Database::factory($settings, $name, $interface);

Where:

*Settings: The connection settings required by the database interface (given as an array, same structure as before).
*Name: The instance name, this can be anything, choosing pre-existing instance names will cause them to be overwritten.
*Interface: The name of the interface driver. eg. 'mysql', 'pdo' etc.

See the init.php file for an example.

### Getting existing database instances.
Once a database instance has been initiated using the factory method, it will be saved in an instance list. You can
access database instances using the instance() method as shown below:

	Database::instance($name);

Note: The name is 'default' by default, so if you have already instanced a database by the name of 'default', the
following code would be used to retrieve it:

	Database::instance();
	
### Using the query builder.

The query builder works just as it did before.

### Table / Column Classes
There are now seperate classes for tables and columns. These are used for introspection, creating and modifying
the database structure.

The table class consists of properties and methods that control that table. If the table object is constructed using
the get_tables() method (shown below), then the loaded() method will return true. This means that all properties
of the table are from the database schema, and that you will be able to alter / modify / drop the table.

NOTE: To modify tables, you must first retrieve them from the database. This will change in due course, as it should
not be a necessity, i accept that.

The column class is much the same, except its split into a further 5 sub-classes (much like sprig) that better
manage introspection and column properties. These are:

* Database_Table_Column_Binary
* Database_Table_Column_Bool
* Database_Table_Column_Float
* Database_Table_Column_Int
* Database_Table_Column_String

Feel free to take a look and see how they work.

### The get_type($datatype) method.
New to the database library is the get_type method, this is used in introspection to create an appropriate column
class according to a given datatype. Database specific types eg. 'int unsigned' for MySQL are defined in the specific
database interface, (in this case, mysql). If no native datatype is found, it will be overloaded to the default
sql types. If a match is not found, and error is thrown.

### Creating a new table.
Here is some sample code to creating a new database table with a database instance by the name of 'test'. I create
a table called users, with a unique and primary key. Composite keys are also supported.

	$db = Database::instance('test');
	
	$table = new Database_Table($db);
	$table->name = 'users';
	
	$id = $db->get_type('int unsigned');
	$id->name = 'id';
	$id->is_primary = true;
	$id->is_nullable = false;
	$id->is_auto_increment = true;
	
	$email = $db->get_type('varchar');
	$email->datatype[1] = 45; //All datatype params are stored in the first index of the datatype property, this can be an array for multiple params e.g. an ENUM.
	$email->name = 'email';
	
	$pass = $db->get_type('varchar');
	$pass->name = 'password';
	$pass->datatype[1] = 65;
	$pass->is_nullable = false;
	
	$table->add_column($id);
    $table->add_column($email);
    $table->add_column($pass);
            		
    $table->create();
    
    // OR
    
    DB::create($table)
    	->execute();
    
And thats all there is to it!

### Altering a table / column.

	// Add a column
	
	DB::alter($table) // Needs to be loaded table
		->add($column) // Needs to be unloaded column, using clone keyword will create an unloaded clone of a column.
		->execute();

	// Drop a column
	
	DB::alter($table)
		->drop($column) // Needs to be a loaded column object for now im afraid (this will change to a string)
		->execute();
	
	// Rename the table to users_test
	
	DB::alter($table)
		->rename('users_test')
		->execute();

	// Modifying a column

	$email = $db->get_type('varchar');
	$email->datatype[1] = 75;
	$email->name = 'email';
	$email->is_nullable = false;
	$email->is_unique = true;
	
	DB::alter($table)
    	->modify($email, 'email')
    	->execute();
    	
### Dropping a table

	DB::drop($table)
		->execute(); // Needs to be a loaded table object.