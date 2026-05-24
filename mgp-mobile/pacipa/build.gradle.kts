plugins {
    alias(libs.plugins.androidLibrary)
}

android {
    namespace = "ne.pacipa.mgp"
    compileSdk = libs.versions.compileSdk.get().toInt()
    defaultConfig {
        minSdk = libs.versions.minSdk.get().toInt()
        testInstrumentationRunner = "androidx.test.runner.AndroidJUnitRunner"
    }
    compileOptions {
        isCoreLibraryDesugaringEnabled = true
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }
}

dependencies {
    coreLibraryDesugaring(libs.desugar)

    implementation(libs.androidxCoreKtx)
    implementation(libs.androidxWorkRuntime)
    implementation(libs.timber)
    implementation(libs.okHttp)
    implementation(libs.gson)

    testImplementation(libs.junit)
    testImplementation(libs.mockitoKotlin)
}
