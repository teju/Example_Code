# Digits for Android

Using Digits from source in production applications is not officially supported by Fabric.
Please utilize the available binaries.

## Download


Define via Gradle:
```groovy

buildscript {
  repositories {
    mavenCentral()
    maven { url 'https://maven.fabric.io/public' }
  }
  dependencies {
    classpath 'io.fabric.tools:gradle:1.+'
  }
}

apply plugin: 'io.fabric'

repositories {
  mavenCentral()
  maven { url 'https://maven.fabric.io/public' }
}

dependencies {
  compile('com.digits.sdk.android:digits:1.11.2@aar') {
    transitive = true
  }
}

```

Check out [more details and other build tool integrations](https://fabric.io/downloads/build-tools)

## Getting Started

* Sign up for a [Fabric account](https://fabric.io) and follow onboarding instructions to get your Fabric API Key and build secret, found under the organization settings of the Fabric web dashboard.
* Get your Digits API key from the IDE plugin.
* Rename samples/app/fabric.properties.sample to samples/app/fabric.properties and populate information.
* Run Sample app to verify build.
* For extensive documentation, please see the [official documentation](http://docs.fabric.io/android/digits/index.html).

## Code of Conduct

This, and all github.com/twitter projects, are under the [Twitter Open Source Code of Conduct](https://engineering.twitter.com/opensource/code-of-conduct). Additionally, see the [Typelevel Code of Conduct](http://typelevel.org/conduct) for specific examples of harassing behavior that are not tolerated.

## Building

Please use the provided gradle wrapper to build the project.

```
./gradlew assemble
```

Run all automated tests on device to verify.

```
./gradlew connectedCheck
```

To run the sample app

```
./gradlew :samples:app:installDebug
```


Contributing

The master branch of this repository contains the latest stable release of Digits. See
[CONTRIBUTING.md](https://github.com/twitter/digits-android/blob/master/CONTRIBUTING.md) for
more details about how to contribute.

## Contact

For usage questions post on [Digits Community](https://twittercommunity.com/c/fabric/digits).

Please report any bugs as [issues](https://github.com/twitter/digits-android/issues).

Follow [@Digits](http://twitter.com/digits) on Twitter for updates.

## Authors

* [Ashwin Raghav](https://twitter.com/ashwinraghav)
* [Eric Frohnhoefer](https://twitter.com/ericfrohnhoefer)
* [Israel Camacho](https://twitter.com/rallat)

Thanks for assistance and contributions:

* [Andre Pinter](https://twitter.com/endform)
* [Dalton Hubble](https://twitter.com/dghubble)
* [Justin Starry](https://twitter.com/sirstarry)
* [Lien Mamitsuka](https://twitter.com/lienm)
* [Megha Bangalore](https://twitter.com/megha)
* [Ty Smith](https://twitter.com/tsmith)
* [Yohan Hartanto](https://twitter.com/yohan)

## License

Copyright 2015 Twitter, Inc.

Licensed under the Apache License, Version 2.0: http://www.apache.org/licenses/LICENSE-2.0
