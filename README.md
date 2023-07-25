# VIP Go mu-plugins

This is the development repo for mu-plugins on [VIP Go](https://wpvip.com/documentation/vip-go/).

## Documentation

### Enterprise Search

Please, visit our [Enterprise Search documentation](https://docs.wpvip.com/how-tos/vip-search/) to learn more.

## Development

### Local Dev

We recommend using the VIP local development environment for local development: https://docs.wpvip.com/technical-references/vip-local-development-environment/

We also have to ensure that we have our dependencies installed - so first, run the following:

```bash
git submodule update --init --recursive
composer install
npm install
```

To use mu-plugins code in a "hot-reload" fashion you need to specify the local folder to which this repository is cloned. For example:

```
vip dev-env create --mu-plugins $(pwd)
```

You will be prompted to configure other options of the environment. When the environment is created you can start it with:

```
vip dev-env start
```

### Tests

##### PHP Lint

```bash
npm run phplint
```

##### PHPCS

We use eslines to incrementally scan changed code. It will automatically run on pre-push (see `.huskyrc.json`).

This is also run on Circle CI for all PRs.

If you want to scan the entire codebase:

```bash
npm run phpcs
```

##### PHPUnit

If you don't have the Lando-based environment running (e.g. in a CI context), we have a script that runs unit tests in a self-contained Docker environment. To run these tests, execute the following from the project root:

```bash
./bin/test.sh
```

You can also filter by test name.
```bash
./bin/test.sh --filter test__rate_limit_ep_query_integration__clears_start_correctly
```

See [`./bin/test.sh`](./bin/test.sh) for more options.

##### CI

PHP Linting and PHPUnit tests are run by Circle CI as part of PRs and merges. See [`.circleci/config.yml`](https://github.com/Automattic/vip-go-mu-plugins/blob/master/.circleci/config.yml) for more.

##### Core tests

We run core tests as part of the CI pipeline. There are many failures when running with mu-plugins so we had to ignore several tests. To add another test there check `bin/utils.sh`.

To investigate failing test locally you can do following (buckle up as this is not so easy:()):

1. While in your mu-plugins folder do `MU_PLUGINS_DIR=$(pwd)`

1. Switch to where you want to checkout core code e.g. `cd ~/svn/wp`

1. Checkout the core code (pick the latest version): `svn co --quiet --ignore-externals https://develop.svn.wordpress.org/tags/5.5.3 .`

1. Create test config: `cp wp-tests-config-sample.php wp-tests-config.php && sed -i 's/youremptytestdbnamehere/wordpress_test/; s/yourusernamehere/root/; s/yourpasswordhere//; s/localhost/127.0.0.1/' wp-tests-config.php`

1. Build core `npm ci && npm run build`

1. Export env variable `export WP_TESTS_DIR="$(pwd)/tests/phpunit"`

1. Start local DB: `docker run -d -p 3306:3306 circleci/mariadb:10.2`

1. Create empty DB `mysqladmin create wordpress_test --user="root" --password="" --host="127.0.0.1" --protocol=tcp`

1. Copy over MU-plugins `cp -r $MU_PLUGINS_DIR build/wp-content/mu-plugins`

1. Run the test you want (in this case `test_allowed_anon_comments`) `$MU_PLUGINS_DIR/vendor/bin/phpunit --filter test_allowed_anon_comments`

## Deployment

### Release

A new release of the plugin consists of all those pull requests that have been merged since the last release and have been deployed to Staging (i.e. have the _[Status] Deployed to staging_ label. Releases are named after the day they are released plus a minor version:

```
YYYYMMDD.x

e.g: 20210917.0
```

Releases are created using GitHub's releases and are effectively a tag in the GitHub repository. Previous releases can be found [here](https://github.com/Automattic/vip-go-mu-plugins/releases/).

To create a new production release:

1. Create a new PR: https://github.com/Automattic/vip-go-mu-plugins/compare/production...staging
2. Name it `Production release: vYYYYMMDD.0`.
3. After carefully reviewing and making sure all test have passed, merge it.
4. The changelog will be generated automatically, and a bot will ping you to proof-read the draft
5. Any follow-up releases on the same day should increment the last number. E.g. `Production release: vYYYYMMDD.0`

To create a new staging release, follow the same steps but name the release `Staging release: vYYYYMMDD.1` (assuming production release has been tagged already.)

https://github.com/Automattic/vip-go-mu-plugins/compare/staging...develop


### Production

**For Automattic Use:** Instructions are in the FG :)

### vip-go-mu-plugins-built

This is a repo primarily meant for local non-development use.

Every commit merged into `develop` is automatically pushed to the public copy at [Automattic/vip-go-mu-plugins-built](https://github.com/Automattic/vip-go-mu-plugins-built/). This is handled via CI by the [`deploy` action](https://github.com/Automattic/vip-go-mu-plugins/blob/develop/.github/workflows/deploy.yml), which pushes a copy of this repo and expanded submodules.

## Changelog

We use a [script](./ci/changelog-summary.php) to generate changelog entries. This can be debuged by running:

```
php ci/changelog-summary.php  --debug --dry-run --force --merge-pr 4673 --github-project-username Automattic --github-project-reponame vip-go-mu-plugins
```
