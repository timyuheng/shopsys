# Guidelines for Dependencies

The whole article is about composer dependencies and how to configure which `composer.json`.

## External Dependencies

We use asterisk `*` notation for php extensions.
We use caret `^` notation for external dependencies and if there is more major versions possible, we use single pipe `|` notation without spaces.
For example `^6.2.0`, `^7.0`, `^5.0|^6.0|^7.0.4`.

If there is a problem, you can stabilize the dependency in a patch version in a separate commit with explanation in the commit message.

## Packages

We specify dependencies only in packages and project-base.

You can check dependencies by phing target `./phing composer-check-dependencies-all` that checks transitive dependencies.
Or you can check one package by specifying the directory like `./phing composer-check-dependencies -D directory=project-base`.

### Limits of `composer-check-dependencies`

The check tool relies on `psr-4` autoload in dependencies, if the dependency is not `psr-4` autoloaded, the tool raises false positives.
So if you have such dependency, add list of unknown symbols into `...package.../composer-check-dependencies-ignore.txt` in alphabetical order.

If we depend on symfony package and also one dependency depends on the whole symfony, the tool raises false positives.
You have to depend on the whole symfony to solve this issue.

When the check finishes, it cleans `vendor` and `composer.lock` in a given package.

The tool is slow, because it have to install `vendor` first and then the check can take time too.

## Monorepo

The dependency management is solved by `wikimedia/composer-merge-plugin` in monorepo, it merges dependencies from packages and project-base.
This means sections `require` and `require-dev` are almost empty and we do not also support `shopsys/shopsys` as a dependency.

The merge plugin report conflicts but unfortunately it doesn't report where the conflict occures.
So finding the conflict is a bit difficult because we have to go through all files `composer.json`.
