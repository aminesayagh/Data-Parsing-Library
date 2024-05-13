# PHP Data Parsing Library (Zod-Like)

The Zod Data Parsing and schema declaration Library is designed to bring the robust schema definition and validation capabilities similar to those in the Zod library (JavaScript) to PHP applications. This library offers flexible, modular, and extensible data validation tools that empower developers to enforce strict data integrity rules easily and reliably.

**Current Version:** v1.0.0

**Note:** This library is currently in development. Official documentation and further resources will be available soon.

**Author Information:**

- [LinkedIn](https://www.linkedin.com/in/mohamedamine-sayagh/)
- [GitHub](https://github.com/aminesayagh/)
- [Portfolio](https://www.masayagh.com/)

## Table of Contents

- [Introduction](#introduction)
- [Installation](#installation)
- [Collaboration](#collaboration)
- [How to Contribute](#how-to-contribute)
- [Usage](#usage)
  - [Basic Parsing](#basic-parsing)
  - [Safe Parsing](#safe-parsing)
  - [Validation](#validation)
  - [Exception Handling with Parsing](#exception-handling-with-parsing)
  - [Parsing of an array of options](#parsing-of-an-array-of-options)
- [License](#license)

## Introduction

The Zod Data Parsing and schema declaration Library is designed to bring the robust schema definition and validation capabilities similar to those in the Zod library (JavaScript) to PHP applications. This library offers flexible, modular, and extensible data validation tools that empower developers to enforce strict data integrity rules easily and reliably.

### Some other great aspects

- **Robust Schema Validation**: Provides strong typing and validation similar to the Zod library, ensuring accurate data handling.
- **Modular and Extensible**: Easy to extend with custom parsers, accommodating diverse data validation needs.
- **Separation of Concerns**: Keeps data parsing separate from business logic for better security and maintainability.
- **Ease of Testing**: Simplifies testing by enforcing strict data validation.
- **Flexible Configuration**: Supports dynamic parser configurations and customizations without altering the core library.
- **Community-Focused**: Open for contributions, enhancing collective improvement and support.

## Installation

**Note: The PHP Data Parsing Library is currently under active development and is not yet ready for production use.**

We appreciate your interest and encourage you to watch this space for updates. Once the library reaches a stable release, installation instructions will be provided here to help you get started with integrating it into your PHP projects.

## Collaboration

**We are fully open to collaboration!** If you're interested in contributing to the PHP Data Parsing Library, we'd love to hear from you. Whether it's through code contributions, documentation improvements, or feature suggestions, your input is highly welcome.

## How to Contribute

1. **Fork the Repository**: Start by forking the repository to your own GitHub account.
2. **Clone Your Fork**: Clone your fork to your local machine and set up the development environment.
3. **Create a New Branch**: Make your changes in a new git branch.
4. **Commit Your Changes**: Commit your modifications with clear and descriptive commit messages.
5. **Push Your Changes**: Push your changes to your fork on GitHub.
6. **Submit a Pull Request**: Open a pull request to the main branch of our repository. Please provide a clear description of the changes and any other information that will help us understand your contributions.

For more details, check our contribution guidelines (link to detailed contribution guidelines if available).

We look forward to building a powerful data parsing tool with a vibrant community of contributors!

## Usage

### Basic Parsing

Define and validate data types using simple schema definitions:

```php
    use function Zod\z;
    $my_schema = z()->string();
    
    $response_valid = $my_schema->parse("Hello, World!"); // Returns Zod data object
    $value = $response_valid->value; // Returns "Hello, World!"

    $response_invalid = $my_schema->parse(123); // Returns Zod data object
    $errors = $response_invalid->errors; // Returns an array of Zod error object
```

### Safe Parsing

Use safe_parse for error-safe parsing, returning structured success/error responses:

```php
    $response = $my_schema->safe_parse("Hello, World!"); // Returns Zod data object
    $value = $response->value; // Returns "Hello, World!"
    $errors = $response->errors; // Returns null
```

### Validation

Quickly validate data after parsing:

```php
    $is_valid = $my_schema->parse("Hello, World!")->is_valid(); // Returns true
    $is_invalid = $my_schema->parse(123)->is_valid(); // Returns false
```

### Exception Handling with Parsing

Handle exceptions using parse_or_throw for critical data validation:

```php
    try {
        $response = $my_schema->parse_or_throw(123); // Throws ZodError
    } catch (ZodError $error) {
        $error_message = $error->message; // Returns "Invalid type: expected string, received integer"
    }
```

### Parsing of an array of options

You can enhance the schema definition by incorporating additional validation options. This allows for more detailed control over data conformity based on specific requirements.

```php
    use function Zod\z;

    $user_schema = z()->options([
        'name' => z()->string()->min(3)->max(50),
        'email' => z()->email(),
        'age' => z()->number()->min(18),
        'isActive' => z()->boolean(),
        'registrationDate' => z()->date()
    ]);

    // Parsing a valid user object
    $valid_user = $user_schema->parse([
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'age' => 30,
        'isActive' => true,
        'registrationDate' => '2021-01-01'
    ]);

    // Parsing an invalid user object
    $invalid_user = $user_schema->parse([
        'name' => 'JD', // Too short
        'email' => 'john.doe@', // Invalid email format
        'age' => 17, // Below minimum age requirement
        'isActive' => 'yes', // Incorrect type (should be boolean)
        'registrationDate' => '01-01-2021' // Wrong date format
    ]);

    // Handling validation results
    if ($valid_user->is_valid()) {
        echo 'User is valid.';
    } else {
        echo 'User is invalid. Errors: ';
        var_dump($valid_user->get_errors());
    }

    if ($invalid_user->is_valid()) {
        echo 'User is valid.';
    } else {
        echo 'User is invalid. Errors: ';
        var_dump($invalid_user->get_errors());
    }
```

## License
