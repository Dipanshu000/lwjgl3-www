import React from 'react'
import { connect } from 'react-redux'
import { MODE_ZIP } from '../constants'

import { configSave } from '../actions'

@connect(
  ({build}) => {
    if ( build.mode === MODE_ZIP ) {
      return {
        mode: MODE_ZIP
      }
    }

    const selected = [];

    build.artifacts.allIds.forEach(artifact => {
      if ( build.contents[artifact] && build.availability[artifact] ) {
        selected.push(artifact);
      }
    });

    return {
      build: build.build,
      mode: build.mode,
      version: build.version,
      hardcoded: build.hardcoded,
      compact: build.compact,
      artifacts: build.artifacts.byId,
      addons: build.addons,
      selected,
      selectedAddons: build.selectedAddons
    };
  },
  {
    configSave
  }
)
class BuildScript extends React.Component {

  copyToClipboard = () => {
    const selection = window.getSelection();
    if ( !selection ) {
      alert('Copying to clipboard not supported!');
      return;
    }
    if ( selection.rangeCount > 0) {
      selection.removeAllRanges();
    }
    const range = document.createRange();
    range.selectNode(this.script);
    selection.addRange(range);
    document.execCommand("copy");
    selection.removeAllRanges();
    alert('Script copied to clipboard.');
    this.props.configSave();
  };

  render() {
    const {mode} = this.props;

    if ( mode === MODE_ZIP ) {
      return null;
    }

    const script = generateScript(this.props);

    return (
      <div>
        <h2 className="mt-1">
          {
            mode === 'maven'
            ? <img src="/svg/maven.svg" alt="Maven" style={{height:60}} />
            : <img src="/svg/gradle.svg" alt="Gradle" style={{height:60}} />
          }
        </h2>
        <pre ref={el => {this.script=el}}><code>{script}</code></pre>
        <div className="download-toolbar">
            <a
              className="btn btn-success"
              download={filename(mode)}
              href={`data:${mime(mode)};base64,${btoa(script)}`}
              onClick={this.props.configSave}
              disabled={!window.btoa}
            >
              DOWNLOAD SCRIPT
            </a>
            <button className="btn btn-success" onClick={this.copyToClipboard} disabled={!document.execCommand}>
              COPY TO CLIPBOARD
            </button>
        </div>
      </div>
    )
  }

}

const filename = mode => mode === 'maven' ? 'pom.xml' : 'build.gradle';
const mime = mode => mode === 'maven' ? 'text/xml' : 'text/plain';
const getVersion = (version, build) => build === "release" ? version : `${version}-SNAPSHOT`;

function generateScript(props) {
  if ( props.mode === 'maven' ) {
    return generateMaven(props);
  } else {
    return generateGradle(props);
  }
}

function generateMaven(props) {
  const {build, hardcoded, compact, artifacts, selected, addons, selectedAddons} = props;
  const version = getVersion(props.version);
  let script = '';
  let nativesBundle = '';
  const v = hardcoded ? version : '\${lwjgl.version}';
  const nl1 = compact ? '' : '\n\t';
  const nl2 = compact ? '' : '\n\t\t';
  const nl3 = compact ? '' : '\n\t\t\t';

  if ( !hardcoded ) {
    script += `<properties>
\t<maven.compiler.source>1.8</maven.compiler.source>
\t<maven.compiler.target>1.8</maven.compiler.target>
\t<lwjgl.version>${version}</lwjgl.version>`;

    selectedAddons.forEach(addon => {
      script += `\n\t<${addon}.version>${addons.byId[addon].maven.version}<${addon}.version>`;
    });

    script += `\n</properties>\n`;
  }

  script += `<profiles>
\t<profile>${nl2}<id>lwjgl-natives-linux></id>${nl2}<activation>${nl3}<os><family>unix</family></os>${nl2}</activation>${nl2}<properties>${nl3}<lwjgl.natives>natives-linux</lwjgl.natives>${nl2}</properties>${nl1}</profile>
\t<profile>${nl2}<id>lwjgl-natives-macos></id>${nl2}<activation>${nl3}<os><family>mac</family></os>${nl2}</activation>${nl2}<properties>${nl3}<lwjgl.natives>natives-macos</lwjgl.natives>${nl2}</properties>${nl1}</profile>
\t<profile>${nl2}<id>lwjgl-natives-windows></id>${nl2}<activation>${nl3}<os><family>windows</family></os>${nl2}</activation>${nl2}<properties>${nl3}<lwjgl.natives>natives-windows</lwjgl.natives>${nl2}</properties>${nl1}</profile>
</profiles>
`;

  if ( build !== "release" ) {
    script += `<repositories>
\t<repository>
\t\t<id>sonatype-snapshots</id>
\t\t<url>https://oss.sonatype.org/content/repositories/snapshots</url>
\t\t<releases><enabled>false</enabled></releases>
\t\t<snapshots><enabled>true</enabled></snapshots>
\t</repository>
</repositories>
`;
  }

  script += `<dependencies>`;

  selected.forEach(artifact => {
    script += `\n\t<dependency>${nl2}<groupId>org.lwjgl</groupId>${nl2}<artifactId>${artifact}</artifactId>${nl2}<version>${v}</version>${nl1}</dependency>`;
    if ( artifacts[artifact].natives !== undefined ) {
      nativesBundle += `\n\t<dependency>${nl2}<groupId>org.lwjgl</groupId>${nl2}<artifactId>${artifact}</artifactId>${nl2}<version>${v}</version>${nl2}<classifier>\${lwjgl.natives}</classifier>${nl2}<scope>runtime</scope>${nl1}</dependency>`;
    }
  });

  script += nativesBundle;

  selectedAddons.forEach(addon => {
    const maven = addons.byId[addon].maven;
    script += `\n\t<dependency>${nl2}<groupId>${maven.groupId}</groupId>${nl2}<artifactId>${maven.artifactId}</artifactId>${nl2}<version>${hardcoded ? maven.version : `\${${addon}.version}`}</version>${nl1}</dependency>`;
  });

  script += `\n</dependencies>`;

  return script;
}

function generateGradle(props) {
  const {build, hardcoded, artifacts, selected, addons, selectedAddons} = props;
  const version = getVersion(props.version);
  let script = '';
  let nativesBundle = '';
  const v = hardcoded ? version : '\${lwjglVersion}';

  script += `import org.gradle.internal.os.OperatingSystem

switch ( OperatingSystem.current() ) {
\tcase OperatingSystem.WINDOWS:
\t\tproject.ext.lwjglNatives = "natives-windows"
\t\tbreak
\tcase OperatingSystem.LINUX:
\t\tproject.ext.lwjglNatives = "natives-linux"
\tbreak
\tcase OperatingSystem.MAC_OS:
\t\tproject.ext.lwjglNatives = "natives-macos"
\t\tbreak
}\n\n`;

  if ( !hardcoded ) {
    script += `project.ext.lwjglVersion = "${version}"\n`;
    selectedAddons.forEach(addon => {
      const maven = addons.byId[addon].maven;
      script += `project.ext.${addon}Version = "${maven.version}"\n`;
    });
    script += `\n`;
  }

  script += `repositories {`;

  if ( build === 'release' || selectedAddons.length ) {
    script += `\n\tmavenCentral()`;
  }
  if ( build !== 'release' ) {
    script += `\n\tmaven { url "https://oss.sonatype.org/content/repositories/snapshots/" }`;
  }
  script += `\n}\n\n`;

  script += `dependencies {`;

  selected.forEach(artifact => {
    script += `\n\tcompile "org.lwjgl:${artifact}:${v}"`;
    if ( artifacts[artifact].natives !== undefined ) {
      nativesBundle += `\n\truntime "org.lwjgl:${artifact}:${v}:\${lwjglNatives}"`;
    }
  });

  script += nativesBundle;

  selectedAddons.forEach(addon => {
    const maven = addons.byId[addon].maven;
    script += `\n\tcompile "${maven.groupId}:${maven.artifactId}:${hardcoded ? maven.version : `\${${addon}Version}`}"`;
  });

  script += `\n}`;

  return script;
}

export default BuildScript
