<?php
declare(strict_types=1);

namespace Zec\Parsers;
use Zec\CONST\FIELD as FK;
use Zec\Zec;

if (!class_exists('ParserBuild')) {
    class ParserBuild
    {
        private string|null $name = null;
        private int $priority = 10;
        private bool $to_log = true;
        private bool $is_init_state = true;
        private array $prioritize = [];
        private array $parser_arguments = [];
        private mixed $default_argument = [];
        private mixed $parser_callback = null;
        public function __construct()
        {
            $this->argument('message', 'Invalid value', function (Zec $z) {
                return $z->required()->string();
            });
        }
        public function name(string $name): ParserBuild
        {
            if (!is_string($name)) {
                throw new \Exception('Name must be a string');
            }
            $this->name = $name;
            return $this;
        }
        public function prioritize(...$prioritize): ParserBuild
        {
            if (empty($prioritize)) {
                throw new \Exception('Prioritize must not be empty');
            }
            if (!is_array($prioritize)) {
                throw new \Exception('Prioritize must be an array');
            }
            foreach ($prioritize as $p) {
                if (!is_string($p)) {
                    throw new \Exception('Prioritize must be a string');
                }
                $this->prioritize[] = $p;
            }
            $this->is_init_state = false;
            return $this;
        }
        public function unLog(): ParserBuild
        {
            $this->to_log = false;
            return $this;
        }
        /**
         * Set the priority for the parser build.
         *
         * @param int $priority The priority value to set.
         * @return ParserBuild The current instance of the ParserBuild.
         * @throws \Exception If the priority is less than 0 or greater than 100.
         */
        public function priority(int $priority): ParserBuild
        {
            if ($priority < 0) {
                throw new \Exception('Priority must be greater than or equal to 0');
            }
            if ($priority > 100) {
                throw new \Exception('Priority must be less than or equal to 100');
            }
            $this->priority = $priority;
            return $this;
        }
        /**
         * Adds an argument to the parser build.
         *
         * @param string $name The name of the argument.
         * @param mixed $default The default value of the argument.
         * @param mixed $parserArgument The parser argument.
         * @return ParserBuild Returns the current instance of the ParserBuild.
         * @throws \Exception If the argument name is not a string, the default argument is not an array, string, integer, boolean, or null, or the parser argument is not callable.
         */
        public function argument(string $name, mixed $default, mixed $parserArgument): ParserBuild
        {
            if (!is_string($name)) {
                throw new \Exception('Argument name must be a string');
            }
            if (!is_array($default) && !is_string($default) && !is_int($default) && !is_bool($default) && $default !== null) {
                throw new \Exception('Default argument must be an array, string, integer, boolean, or null');
            }
            if (!is_callable($parserArgument)) {
                throw new \Exception('Parser argument must be a callable');
            }
            $this->default_argument[$name] = $default;
            $this->parser_arguments[$name] = $parserArgument;
            return $this;
        }
        /**
         * Sets the parser callback function.
         *
         * @param callable $callback The callback function to be set as the parser callback.
         * @return ParserBuild Returns the current instance of the ParserBuild class.
         * @throws \Exception Throws an exception if the provided callback is not callable.
         */
        public function parserCallback(callable $callback): ParserBuild
        {
            if (!is_callable($callback)) {
                throw new \Exception('Parser callback must be a callable');
            }
            $this->parser_callback = $callback;
            return $this;
        }
        /**
         * Builds and returns an array representing the parser configuration.
         *
         * @throws \Exception if any required properties are null or empty
         *
         * @return array The built parser configuration
         */
        public function build(): array {
            if ($this->name == null) {
                throw new \Exception('Name must not be null');
            }
            if (empty($this->parser_arguments)) {
                throw new \Exception('Parser arguments must not be empty');
            }
            if ($this->parser_callback == null) {
                throw new \Exception('Parser callback must not be null');
            }
            if (!is_array($this->prioritize)) {
                throw new \Exception('Prioritize must not be empty');
            }
            if (empty($this->default_argument)) {
                throw new \Exception('Default argument must not be empty');
            }
            $build_argument = [];
            $build_argument[FK::NAME] = $this->name;
            $build_argument[FK::PRIORITY] = $this->priority;
            $build_argument[FK::PRIORITIZE] = $this->prioritize;
            $build_argument[FK::PARSER_ARGUMENTS] = function (Zec $z) use ($build_argument) {
                $args = [];
                foreach ($this->parser_arguments as $name => $parserArgument) {
                    $args[$name] = $parserArgument($z);
                }
                return $z->options($args);
            };
            $build_argument[FK::DEFAULT_ARGUMENT] = $this->default_argument;
            $build_argument[FK::PARSER_CALLBACK] = $this->parser_callback;
            $build_argument[FK::IS_INIT_STATE] = $this->is_init_state;
            $build_argument['signed'] = $this::class;
            $build_argument[FK::LOG_ERROR] = $this->to_log;
            return $build_argument;
        }
    }
}

if (!function_exists('parser_build')) {
    function parser_build(): ParserBuild {
        return new ParserBuild();
    }
}