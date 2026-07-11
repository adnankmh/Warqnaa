from pathlib import Path

kotlin = Path('android/app/build.gradle.kts')
groovy = Path('android/app/build.gradle')

if kotlin.exists():
    text = kotlin.read_text(encoding='utf-8')
    prefix = """import java.util.Properties
import java.io.FileInputStream

val keystoreProperties = Properties()
val keystorePropertiesFile = rootProject.file(\"key.properties\")
if (keystorePropertiesFile.exists()) {
    keystoreProperties.load(FileInputStream(keystorePropertiesFile))
}

"""
    if 'val keystoreProperties = Properties()' not in text:
        text = prefix + text
    signing = """    signingConfigs {
        create(\"release\") {
            keyAlias = keystoreProperties[\"keyAlias\"] as String
            keyPassword = keystoreProperties[\"keyPassword\"] as String
            storeFile = file(keystoreProperties[\"storeFile\"] as String)
            storePassword = keystoreProperties[\"storePassword\"] as String
        }
    }

"""
    if 'create("release")' not in text:
        text = text.replace('    buildTypes {', signing + '    buildTypes {', 1)
    text = text.replace(
        'signingConfig = signingConfigs.getByName("debug")',
        'signingConfig = signingConfigs.getByName("release")',
    )
    kotlin.write_text(text, encoding='utf-8')
elif groovy.exists():
    text = groovy.read_text(encoding='utf-8')
    prefix = """def keystoreProperties = new Properties()
def keystorePropertiesFile = rootProject.file('key.properties')
if (keystorePropertiesFile.exists()) {
    keystoreProperties.load(new FileInputStream(keystorePropertiesFile))
}

"""
    if 'def keystoreProperties = new Properties()' not in text:
        text = prefix + text
    signing = """    signingConfigs {
        release {
            keyAlias keystoreProperties['keyAlias']
            keyPassword keystoreProperties['keyPassword']
            storeFile file(keystoreProperties['storeFile'])
            storePassword keystoreProperties['storePassword']
        }
    }

"""
    if 'signingConfigs {' not in text:
        text = text.replace('    buildTypes {', signing + '    buildTypes {', 1)
    text = text.replace('signingConfig signingConfigs.debug', 'signingConfig signingConfigs.release')
    groovy.write_text(text, encoding='utf-8')
else:
    raise SystemExit('Android Gradle application file not found.')
