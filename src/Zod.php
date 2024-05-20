<?php
declare(strict_types=1);

namespace Zod;

use Zod\PARSERS_KEY as PK; // PK: Parser Key

require_once ZOD_PATH . '/src/parsers/Parser.php';
require_once ZOD_PATH . '/src/parsers/Parsers.php';
require_once ZOD_PATH . '/src/Exception.php';

if(!interface_exists('IZod')) {
    interface IZod {
        public function __construct(array $parsers = [], ZodErrors $_errors = new ZodErrors());
        public function __clone();
        public function __call(string $name, array $arguments): mixed;
        public function parse(mixed $value, mixed $default = null): Zod;
        public function set_default(mixed $default): Zod;
        public function pick(string $name): ?Zod;
    }
}

if(!class_exists('ZodPath')) {
    class ZodPath {
        private array $_path = [];
        public function __construct(ZodPath|string $path = null) {
            if (!is_null($path)) {
                if (is_string($path)) {
                    $this->_path = explode('.', $path);
                } else {
                    $this->_path = $path->get_path();
                }
            }
        }
        public function get_path(): array {
            return $this->_path;
        }
        public function extend(ZodPath $path): void {
            $this->_path = array_merge($this->_path, $path->get_path());
        }
        public function push(string $key): void {
            $this->_path[] = $key;
        }
        public function pop(): void {
            $head = $this->_path[0];
            array_pop($this->_path);
            return $head;
        }
        public function get_path_string(): string {
            return implode('.', $this->_path);
        }
    }
}

if(!class_exists('Zod')) {
    class Zod extends Parsers implements IZod {
        /**
         * Holds the array of errors encountered during validation.
         *
         * @var ZodErrors
         */
        private ZodErrors $_errors;
        private mixed $_value = null;
        private mixed $_default = null;
        private ZodPath $_path;
        private array $_conf = [
            'trust_arguments' => false,
        ];
        private Zod|null $_parent = null;
        public function __construct(array $parsers = [], ZodErrors $_errors = new ZodErrors()) {
            parent::__construct($parsers);
            $this->_errors = $_errors ?? new ZodErrors();
            $this->_path = new ZodPath();
        }
        public function set_trust_arguments(bool $trust_arguments): Zod {
            $this->_conf['trust_arguments'] = $trust_arguments;
            return $this;
        }
        public function set_key_flag(string $flag): Zod {
            $this->_path->push($flag);
            return $this;
        }
        public function remove_key_flag(string $flag): Zod {
            $this->_path->pop();
            return $this;
        }
        public function get_configs(): array {
            return $this->_conf;
        }
        public function get_conf(string $key): mixed {
            return $this->_conf[$key];
        }
        public function __get($name) {
            if (array_key_exists($name, $this->_conf)) {
                return $this->_conf[$name];
            }
        }
        /**
         * Creates a clone of the Zod object.
         *
         * This method is used to create a deep copy of the Zod object, including its internal state.
         * It clones the `_errors` property and filters the `parsers` array, removing any null values
         * and parsers with the key 'required'. It also clones each parser in the filtered array.
         *
         * @return void
         */
        public function __clone(): void {
            $this->_errors = clone $this->_errors;
            $this->parsers = array_filter($this->parsers, function($parser) {
                if (is_null($parser)) {
                    return false;
                }
                if ($parser->name == 'required') {
                    return false;
                }
                return $parser->clone();
            });
        }
        /**
         * Dynamically handles method calls for the Zod class.
         *
         * @param string $name The name of the method being called.
         * @param mixed $arguments The arguments passed to the method.
         * @return mixed The result of the method call.
         * @throws ZodError If the method or parser is not found.
         */
        public function __call(string $name, ?array $arguments): mixed{

            if (method_exists($this, $name)) {
                return call_user_func_array([$this, $name], $arguments);
            }
            if (bundler()->has_parser_key($name)) {
                // echo "Parser $name found" . PHP_EOL;
                $parser = bundler()->get_parser($name);
                if(is_array($arguments) && count($arguments) > 0) {
                    $arguments = $arguments[0];
                    $parser->set_argument($arguments);
                }
                
                $this->add_parser($parser);
                return $this;
            }

            throw new ZodError("Method $name not found");
        }
        private function get_conf_from_parent(Zod|null $parent): void {
            if (is_null($parent)) {
                return;
            }
            $this->_conf = array_merge(
                $this->_conf,
                $parent->get_configs()
            );
        }
        public function get_value() {
            return $this->_value;
        }
        /**
         * Parses the given value using the specified parsers.
         *
         * @param mixed $value The value to be parsed.
         * @param mixed $default The default value to be used if the parsed value is null.
         * @param Zod|null $parent The parent Zod instance, if any.
         * @return Zod The current Zod instance.
         */
        public function parse(mixed $value, mixed $default = null, Zod|null $parent = null): Zod {
            $this->_errors->reset();

            $this->_value = $value;
            $this->set_default($default);

            if (!is_null($parent)) {
            }

            
            $has_required = $this->has_parser_key(PK::REQUIRED);
            if (!$has_required && is_null($value)) {
                return $this;
            }
            
            if (!is_null($parent)) {
                $this->_parent = $parent;
                $this->get_conf_from_parent($parent);
                $this->_path->extend($parent->_path);
            }

            foreach ($this->list_parsers() as $index => $parser) {
                // echo $this->_path->get_path_string() . ' : ' . $parser->name . PHP_EOL;
                if ($index > 0) {
                    $this->_path->pop();
                }
                $this->_path->push($parser->name);
                // echo "Parsing $parser->name : " . json_encode($this->_value) . " " . PHP_EOL;
                $parser->parse($this->_value, [
                    'default' => $this->_default,
                ], $this);
            }

            // push the error to the parent
            if (!is_null($parent)) {
                $parent->set_errors($this->_errors);
            }

            return $this;
        }
        /**
         * Sets the default value for the Zod instance.
         *
         * @param mixed $default The default value to be set.
         * @return Zod The updated Zod instance.
         */
        public function set_default(mixed $default): Zod {
            if(is_null($default)) {
                return $this;
            }
            $parser_of_default = clone $this;
            $parser_of_default->parse_or_throw($default);

            $this->_default = $default;
            return $this;
        }
        public function set_errors(ZodErrors $errors): void {
            foreach ($errors->get_errors() as $error) {
                $this->_errors->set_error($error);
            }
        }
        public function get_path_string(): string {
            return $this->_path->get_path_string();
        }
        public function is_valid(): bool {
            return $this->_errors->is_empty();
        }
        public function get_errors(): ZodErrors {
            return $this->_errors;
        }
        public function get_errors_message(): string {
            return $this->_errors->get_message();
        }
        /**
         * Sets an error for the current Zod instance and its parent, if any.
         *
         * @param ZodError $error The error to be set.
         * @return Zod The current Zod instance.
         */
        public function set_error(ZodError $error): Zod {
            // set the same error to the parent
            if (!is_null($this->_parent)) {
                $this->_parent->set_error($error);
            }

            $this->_errors->set_error($error);
            return $this;
        }
        /**
         * Parses the given value and throws an error if it is not valid.
         *
         * @param mixed $value The value to parse.
         * @param mixed $default The default value to use if the parsed value is invalid.
         * @return Zod The parsed value.
         * @throws mixed The error(s) if the parsed value is invalid.
         */
        public function parse_or_throw(mixed $value, mixed $default = null, Zod $parent = null): mixed {
            $this->parse($value, $default);
            if (!$this->is_valid()) {
                throw $this->_errors;
            }
            return $this->get_value();
        }
        public function pick(string $name): ?Zod {
            $zod_path = new ZodPath($name);
            $pile = $zod_path->get_path();
            $zod = $this;
            $parser_options = $zod->get_parser(PK::OPTIONS);
            
            if (is_null($parser_options)) {
                return null;
            }
            $main_option_argument = $parser_options->get_argument()[PK::OPTIONS];
            
            $first_key = array_shift($pile);
            $zod = $main_option_argument[$first_key];
            if (count($pile) === 0) {
                return $zod;
            }
            return $zod->pick(implode('.', $pile));
        }
    }
}

if (!function_exists('zod')) {
    /**
     * Returns an instance of the Zod class.
     *
     * @param array $parsers The array of parsers to assign to the Zod instance.
     * @param ZodErrors $errors The array of errors to assign to the Zod instance.
     * @return Zod The instance of the Zod class.
     */
    function zod(array $parsers = [], ZodErrors $errors = new ZodErrors()): Zod {
        return new Zod($parsers, $errors);
    }
}

if (!function_exists('is_zod')) {
    /**
     * Checks if the given value is an instance of the Zod class.
     *
     * @param mixed $value The value to check.
     * @return bool Returns true if the value is an instance of Zod, false otherwise.
     */
    function is_zod($value): bool {
        return is_a($value, 'Zod');
    }
}


if (!function_exists('z')) {
    /**
     * Returns an instance of the Zod class.
     *
     * @param array $parsers The array of parsers to assign to the Zod instance.
     * @param ZodErrors $errors The array of errors to assign to the Zod instance.
     * @return Zod The instance of the Zod class.
     */
    function z(array $parsers = [], ZodErrors $errors = new ZodErrors()): Zod {
        return zod($parsers, $errors);
    }
}