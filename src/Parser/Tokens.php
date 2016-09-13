<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Parser;

class TokenFilterIterator implements \Iterator {
    private $ignore;
    private $tokens;

    public function __construct(Tokens $tokens, array $ignore) {
        $this->ignore = $ignore;
        $this->tokens = $tokens;
    }

    public function current() {
        return $this->tokens->current();
    }

    public function key() {
        return $this->tokens->key();
    }

    public function valid() {
        return $this->tokens->valid();
    }

    public function next() {
        do {
            $this->tokens->next();
        }
        while ($this->tokens->valid() && in_array($this->tokens->current()['type'], $this->ignore));
    }

    public function rewind() {
        $this->tokens->rewind();
        while ($this->tokens->valid() && in_array($this->tokens->current()['type'], $this->ignore)) $this->tokens->next();
    }
}
class Tokens implements \Iterator, \Countable {
    private $tokens;
    private $iterator = 0;

    public function __construct(array $tokens) {
        $this->tokens = $tokens;
    }

    public function count() {
        return count($this->tokens);
    }

    // Iterator Functions
    public function current() {
        return $this->tokens[$this->iterator];
    }

    public function key() {
        return $this->iterator;
    }

    public function next() {
        ++$this->iterator;
	}

	public function valid() {
		return isset($this->tokens[$this->iterator]);
	}

	public function rewind() {
		$this->iterator = 0;
	}

    // Helpful Functions
    public function ignoreWhitespace($ignore = false) {
      // throw new \Exception();
       return new Tokens($this->tokens, $ignore);

    }

    private function getKeysOfTokenType($tokenType) {
        return array_keys(array_column($this->tokens, 'type'), $tokenType);
    }

    public function from($tokenType, $inclusive = false) {
        $keys = $this->getKeysOfTokenType($tokenType);
        if (count($keys) === 0) return new Tokens([]);
        $key = $keys[0];
        for ($i = 0; $key < $this->iterator; $i++) $key = $keys[$i];
        if (!$inclusive) $key++;
        return new Tokens(array_slice($this->tokens, $key));
    }

    public function to($tokenType, $inclusive = false) {
        $keys = $this->getKeysOfTokenType($tokenType);
        if (empty($keys)) return new Tokens([]);
        $key = $keys[0];
        for ($i = 0; $key < $this->iterator; $i++) $key = $keys[$i];
        if ($inclusive) $key++;
        return new Tokens(array_slice($this->tokens, 0, $key));
    }

    public function skip($count) {
        $this->iterator += $count;
    }

    public function splitOnToken($tokenType) {
        $splitTokens = [];
		$i = 0;
		foreach ($this->tokens as $token) {
			if ($token['type'] === $tokenType) $i++;
			else $splitTokens[$i][] = $token;
		}
        return array_map(function ($tokens) {
            return new Tokens($tokens);
        }, $splitTokens);
		//return $splitTokens;
    }

    public function trim() {
        $tokens = $this->tokens;
        // Remove end whitespace
        while (end($tokens)['type'] === Tokenizer::WHITESPACE) {
            array_pop($tokens);
        }
        // Remove begining whitespace
        while (isset($tokens[0]) && $tokens[0]['type'] === Tokenizer::WHITESPACE) {
            array_shift($tokens);
        }
        return new Tokens($tokens);
    }

    public function getTokens() {
    //    throw new \Exception();
        $tokens = [];
        //Loop through $this to account for $ignoreWhitespace
        foreach ($this as $token) {
            $tokens[] = $token;
        }
        return $tokens;
    }

    public function read($offset = 0) {
        return isset($this->tokens[$offset]) ? $this->tokens[$offset]['value'] : false;
    }

    public function type($offset = 0) {
        return isset($this->tokens[$offset]) ? $this->tokens[$offset]['type'] : false;   
    }
}
