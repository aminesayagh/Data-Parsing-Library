<?php 

namespace Zod {
    class FIELD {
        const PRIORITIZE = 'prioritize';
        const PARSER_ARGUMENTS = 'parser_arguments';
        const DEFAULT_ARGUMENT = 'default_argument';
        const PRIORITY = 'priority';
        const PARSER_CALLBACK = 'parser_callback';
    }
}

namespace Zod {
    // Get a list of all const element on Zod\PARSER\KEY
    class PARSER {
        const REQUIRED = 'required';
        const OPTIONAL = 'optional';
        const EMAIL = 'email';
        const DATE = 'date';
        const BOOL = 'bool';
        const STRING = 'string';
        const URL = 'url';
        const MIN = 'min';
        const MAX = 'max';
        const NUMBER = 'number';
        const OPTIONS = 'options';
        const EACH = 'each';
    }
}
