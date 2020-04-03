# FeatureFlagBundle

Simple, stupid and yet flexible Feature Flags system for the Symfony world. Please, take 30 minutes to read the tremendous article [Feature Toggles (aka Feature Flags)
](https://www.martinfowler.com/articles/feature-toggles.html). Feature flags are not an easy and small topic. They present great powers but come with many burdens.  

## Why an Akeneo bundle for that?

There are dozens of feature flags libraries for Symfony, React, PHP or Javascript. A few Symfony's ones have been evaluated. They all propose different advanced features that are, at the moment this documentation being written, not useful to us.

The aim of this bundle is not to replace those libraries. But rather to establish a simple and clear contract for our teams. If you want to use an existing library, feel free to do so. You just have to embed it in the contracts described here.

Note: the most promising library for Symfony is probably [FlagceptionBundle](https://github.com/bestit/flagception-bundle). Too bad it doesn't support toggling Symfony services.

## Feature flags' configuration

Feature flags are defined by a _key_, representing the feature, and a _service_ which answers to the question "Is this feature enabled?". 

```yaml
// config/packages/akeneo_feature_flag.yml

akeneo_feature_flag:
    - onboarder: '@service_that_defines_if_onboarder_feature_is_enabled'
    - foo: '@service_that_defines_if_foo_feature_is_enabled'
    - ...
```

The most important here is to decouple the decision point (the place where I need to know if a feature is enabled) from the decision logic (how do I know if this feature is enabled). 

Your feature flag service must respect the following contract:

```php
namespace Akeneo\Platform\Bundle\FeatureFlagBundle;

interface FeatureFlag
{
    public function isEnabled(): bool
}    
```

Your feature flag service must be tagged with `akeneo_feature_flag`.

### Examples

Let's take a very simple example: we want to (de)activate the _Onboarder_ feature via an environment variable. All we have to do is to declare the following service:

```yaml
services:
    service_that_defines_if_onboarder_feature_is_enabled:
        class: 'Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration\EnvVarFeatureFlag'
        arguments:
            - '%env(FLAG_ONBOARDER_ENABLED)%'
        tags: ['akeneo_feature_flag']
```

Behind the scenes, the very simple `EnvVarFeatureFlag` class is called:

```php
namespace Akeneo\Platform\Bundle\FeatureFlagBundle;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

class EnvVarFeatureFlag implements FeatureFlag
{
    private $isEnabled;

    public function __construct(bool $isEnabled)
    {
        $this->isEnabled = $isEnabled;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }
}
``` 

Another example. Imagine now you want to allow Akeneo people working at Nantes to access a beta `foo` feature. All you have to do is declare in your code a service that implements `Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag`.

```yaml
services:
    service_that_defines_if_foo_feature_is_enabled:
        class: 'Akeneo\My\Own\Namespace\FooFeatureFlag'
        arguments:
            - '@request_stack'
        tags: ['akeneo_feature_flag']
``` 

```php
namespace Akeneo\My\Own\Namespace;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

class FooFeatureFlag implements FeatureFlag
{
    private $akeneoIpAddress = //...
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }
    
    public function isEnabled(): bool
    {
        return $this->requestStack->getCurrentRequest()->getClientIp() === $this->$akeneoIpAddress; 
    }
}

```

### Provided feature flag classes

To ease developments, the _FeatureFlagBundle_ comes with a few ready to use implementations. When you want to use those classes, all you have to do is to declare a service.  

- `Akeneo\Platform\Bundle\FeatureFlagBundle\EnvVarFeatureFlag`: know if a feature is activated by checking an environment variable.  
- ...

### About the frontend

Flags are of course also available for frontend. Behind the scenes, a backend route (TODO: ROUTE HERE) is called. It returns a JSON response answering if the feature is enabled or not. See the part _Knowing if a feature is enabled_ for more information.


## Using feature flags in your code

### Knowing if a feature is enabled

#### Backend

A service called `akeneo_feature_flags` exists to determine if the feature you have configured in `config/packages/akeneo_feature_flag.yml` is enabled or not. This is the one and only backend entry point you have to use.

```php
$flags = $container->get('akeneo_feature_flags');
if ($flags->isEnabled('myFeature')) { //...
```

#### Frontend

TODO with Paul: the idea is to have a simple service `AkeneoFeatureFlags`. Maybe it can be embedded in a fetcher to act as a kind of cache.

### Short living feature flags

**Flags that will live from a few days to a few weeks.**

This happens typically when you develop a "small" feature bits by bits. At present, the feature is not ready to be presented to the end user, but with a few more pull requests and tests, this will be the case. 

For those use cases, we'll go simple. Inject the feature flags service (backend or frontend) in your code and branch with a straightforward `if/else`. 

**This way of working works only and only if you clean all those hideous conditionals when your feature is ready to use.** Otherwise, the code will quickly become hell of a maze with all flags setup by all different teams. 

**Also, please take extract care on the impact your flag could have on other teams' flags.** If it becomes tedious, please adopt the same strategy than for long living flags instead.

### Long living feature flags

**Flags that will live more than a few weeks.**

The standard use case for that are premium features, like the _Onboarder_. They will always be present in the code, but won't be enabled for everyone or everytime.

Those flags require extra attention. We must avoid crippling business code with `if/else` branching. Instead, use [inversion of control](https://en.wikipedia.org/wiki/Inversion_of_control) and [Symfony's service factories](https://symfony.com/doc/current/service_container/factories.html) or the [strategy pattern](https://en.wikipedia.org/wiki/Strategy_pattern).

TODO: in this example, we used...

## Part about what we can do when flagging?

TODO: for instance:
- 403 forbidden routes
- ...