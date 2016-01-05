<?php
namespace TYPO3Fluid\Fluid\Core\Variables;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Class ChainedVariableProvider
 *
 * Allows chainging any number of prioritised VariableProviders
 * to be consulted whenever a variable is requested. First
 * VariableProvider to return a value "wins".
 */
class ChainedVariableProvider extends StandardVariableProvider implements VariableProviderInterface {

	/**
	 * @var VariableProviderInterface[]
	 */
	protected $variableProviders = array();

	/**
	 * @param VariableProviderInterface $variableProviders
	 */
	public function __construct(array $variableProviders = array()) {
		$this->variableProviders = $variableProviders;
	}

	/**
	 * @return array
	 */
	public function getAll() {
		$merged = array();
		foreach (array_reverse($this->variableProviders) as $provider) {
			$merged = array_replace_recursive($merged, $provider->getAll());
		}
		return array_merge($merged, $this->variables);
	}

	/**
	 * @param string $identifier
	 * @return mixed
	 */
	public function get($identifier) {
		if (array_key_exists($identifier, $this->variables)) {
			return $this->variables[$identifier];
		}
		foreach ($this->variableProviders as $provider) {
			$value = $provider->get($identifier);
			if ($value !== NULL) {
				return $value;
			}
		}
		return NULL;
	}

	/**
	 * @param string $path
	 * @param array $accessors
	 * @return mixed|null
	 */
	public function getByPath($path, array $accessors = array()) {
		$value = VariableExtractor::extract($this->variables, $path, $accessors);
		if ($value !== NULL) {
			return $value;
		}
		foreach ($this->variableProviders as $provider) {
			$value = $provider->getByPath($path, $accessors);
			if ($value !== NULL) {
				return $value;
			}
		}
		return NULL;
	}

	/**
	 * @return array
	 */
	public function getAllIdentifiers() {
		$merged = parent::getAllIdentifiers();
		foreach ($this->variableProviders as $provider) {
			$merged = array_replace_recursive($merged, $provider->getAllIdentifiers());
		}
		return array_values(array_unique($merged));
	}

	/**
	 * @param array $variables
	 * @return ChainedVariableProvider
	 */
	public function getScopeCopy(array $variables) {
		$clone = clone $this;
		$clone->setSource($variables);
		return $clone;
	}

}
