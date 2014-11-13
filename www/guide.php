<?ob_start()?>
<script src="//cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shCore.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shBrushJava.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shBrushXml.js"></script>
<script>
SyntaxHighlighter.config.tagName = "code";
SyntaxHighlighter.all();
</script>
<?
$pageScript = ob_get_contents();
ob_end_clean();
ob_start();
?>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/styles/shCore.css">
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/SyntaxHighlighter/3.0.83/styles/shThemeRDark.css">
<style>section.codehlt { background-color: #1b2426; padding: 1px 0; }</style>
<?
$pageStyle = ob_get_contents();
ob_end_clean();

$pageTitle = "LWJGL 3 Guide";
include "header.php"
?>

<section class="container">
	<br>
	<h1>LW<b>JGL</b> 3 Guide</h1>

	<p>This guide will help you get started with LWJGL.</p>

	<h3>Getting Started</h3>

	<p>Please use our <a href="http://new.lwjgl.org/download">download page</a> to download an LWJGL release. You will also need a <a href="http://www.oracle.com/technetwork/java/javase/downloads/index.html">Java SE Development Kit</a> (JDK), LWJGL will work on version 7 or newer. Then proceed by setting up a project in your favorite IDE and configuring it like so:
	<ul>
		<li>Add the LWJGL jars to the classpath. This is usually done by setting up a library dependency for your project and attaching jars to it.</li>
		<li>Set the <strong>-Djava.library.path</strong> system property (as a JVM launch argument) to the appropriate path for the target OS/architecture</li>
		<li>Attach the LWJGL javadoc and source archives to the LWJGL library (optional, but hightly recommended)</li>
	</ul>
	</p>
	
	<p>You should now be ready to develop and launch an LWJGL application. Following is a simple example that utilizes GLFW to create a window and clear the background color to red, using OpenGL:</p>
</section>
<section class="container-responsive codehlt">
<code class="brush: java; tab-size: 4; toolbar: false">
import org.lwjgl.Sys;
import org.lwjgl.opengl.*;
import org.lwjgl.system.glfw.*;

import java.nio.ByteBuffer;

import static org.lwjgl.opengl.GL11.*;
import static org.lwjgl.system.MemoryUtil.*;
import static org.lwjgl.system.glfw.GLFW.*;

public class HelloWorld {

	private long window;

	public void execute() {
		System.out.println("Hello LWJGL " + Sys.getVersion() + "!");

		try {
			init();
			loop();
			glfwDestroyWindow(window);
		} finally {
			glfwTerminate();
		}
	}

	private void init() {
		glfwSetErrorCallback(ErrorCallback.Util.getDefault());

		if ( glfwInit() != GL11.GL_TRUE )
			throw new IllegalStateException("Unable to initialize GLFW");

		glfwDefaultWindowHints();
		glfwWindowHint(GLFW_VISIBLE, GL_FALSE);
		glfwWindowHint(GLFW_RESIZABLE, GL_TRUE);

		int WIDTH = 300;
		int HEIGHT = 300;

		window = glfwCreateWindow(WIDTH, HEIGHT, "Hello World!", NULL, NULL);
		if ( window == NULL )
			throw new RuntimeException("Failed to create the GLFW window");

		WindowCallback.set(window, new WindowCallbackAdapter() {
			@Override
			public void key(long window, int key, int scancode, int action, int mods) {
				if ( key == GLFW_KEY_ESCAPE && action == GLFW_RELEASE )
					glfwSetWindowShouldClose(window, GL_TRUE);
			}
		});

		ByteBuffer vidmode = glfwGetVideoMode(glfwGetPrimaryMonitor());
		glfwSetWindowPos(
			window,
			(GLFWvidmode.width(vidmode) - WIDTH) / 2,
			(GLFWvidmode.height(vidmode) - HEIGHT) / 2
		);

		glfwMakeContextCurrent(window);
		glfwSwapInterval(1);

		glfwShowWindow(window);
	}

	private void loop() {
		GLContext.createFromCurrent();

		glClearColor(1.0f, 0.0f, 0.0f, 0.0f);
		while ( glfwWindowShouldClose(window) == GL_FALSE ) {
			glClear(GL_COLOR_BUFFER_BIT | GL_DEPTH_BUFFER_BIT);

			glfwSwapBuffers(window);
			glfwPollEvents();
		}
	}

	public static void main(String[] args) {
		new HelloWorld().execute();
	}

}
</code>
</section>
<section class="container">
	<p>LWJGL is fully documented, you can explore the javadoc documentation online <a href="http://javadoc.lwjgl.org/">starting here</a>. For more information about LWJGL's design choices and overall architecture, visit the <a href="https://github.com/LWJGL/lwjgl3/wiki">wiki</a>. The <a href="https://github.com/LWJGL/lwjgl3/wiki/1.5.-Bindings-FAQ">bindings FAQ</a> page is especially useful.</p>

	<h3>Building from source</h3>

	<p>Clone the <a href="https://github.com/LWJGL/lwjgl3.git">Git repository</a> locally, install JDK 7 and <a href="http://ant.apache.org/">Apache Ant</a>, set the JAVA7_HOME environment variable to point to a JDK 7 installation, then you should be ready to build. Use the following targets:
	<ul>
		<li>ant &ndash; Builds everything and runs the tests</li>
		
		<li>ant compile-templates &ndash; Compiles the binding generator templates</li>
		<li>ant compile &ndash; Compiles the Java code (including generated)</li>
		<li>ant compile-native &ndash; Compiles and links the native library</li>
		<li>ant tests &ndash; Runs the test suite</li>
		<li>ant demo -Dclass=&lt;demo class&gt; &ndash; Runs one of the LWJGL demos in the test module</li>
		
		<li>ant clean &ndash; Deletes all files and folders generated by the build script.</li>
		<li>and -f update_dependencies.xml &ndash; Forces all dependencies to be downloaded again.</li>
	</ul></p>
	
	<p>Note that the target native architecture is determined by <em>os.arch</em> of the JVM that runs Ant. For cross-compiling, use the LWJGL_BUILD_ARCH environment variable to override it (set it to <em>x86</em> or <em>x64</em>).</p>
	
	<p>Binary dependencies are downloaded from the stable download branch. Use the LWJGL_BUILD_TYPE environment variable to override this:
	<ul>
		<li>nightly &ndash; the latest successful build, possibly broken. Dependency repositories can be found <a href="https://github.com/LWJGL-CI">here</a></li>
		<li>stable &ndash; the latest build that has been verified to work with LWJGL, the default</li>
		<li>release/latest &ndash; the latest stable build that has been promoted to an official LWJGL release</li>
		<li>release/{build.version} &ndash; a specific previously released build</li>
	</ul></p>
	
	<p>If you are using custom binaries, or simply need to work offline, set the LWJGL_BUILD_OFFLINE environment variable to one of <em>true/on/yes</em>.</p>
</section>

<br><br>

<div class="area-dark">
	<section class="container">
		<h1>Is LW<b>JGL</b> for me?</h1>

		<p>LWJGL is simple but powerful. It is not for everyone.</p>
		<p>If you're into OpenGL, you'll feel right at home.</p>
		<p>If you're just getting started, please familiarize yourself with each API first.</p>
		<br>
	</section>
</div>

<br><br>

<section class="container">
	<h2>GLFW</h2>
	<p><a href="http://www.glfw.org/">GLFW</a> is an Open Source, multi-platform library for creating windows with OpenGL contexts and receiving input and events. It is easy to integrate into existing applications and does not lay claim to the main loop.</p>
	
	<p>GLFW is the preferred windowing system for LWJGL 3 applications. If you're familiar with LWJGL 2, GLFW is a replacement for the Display class and everything in the input package.</p>
	
	<p>Learning GLFW is easy. It has a simple, yet powerful, API and comprehensive <a href="http://www.glfw.org/docs/latest/">documentation</a>.</p>
</section>
<hr>
<section class="container">
	<h2>OpenGL</h2>
	<p><a href="https://www.opengl.org/about/">OpenGL</a> is the premier environment for developing portable, interactive 2D and 3D graphics applications.</p>
	
	<p>OpenGL is a massive API with long history and hundreds of extensions. Learning it from scratch is no easy undertaking, but you can start from its <a href="https://www.opengl.org/documentation/">documentation</a>. The <a href="https://www.opengl.org/registry/">OpenGL registry</a> is also quite useful.</p>
</section>
<hr>
<section class="container">
	<h2>OpenCL</h2>
	<p><a href="https://www.khronos.org/opencl/">OpenCL</a> is the first open, royalty-free standard for cross-platform, parallel programming of modern processors found in personal computers, servers and handheld/embedded devices. OpenCL (Open Computing Language) greatly improves speed and responsiveness for a wide spectrum of applications in numerous market categories from gaming and entertainment to scientific and medical software.</p>
	
	<p>Specifications for OpenCL and its extensions can be found at the <a href="https://www.khronos.org/registry/cl/">Khronos OpenCL registry</a>.</p>
</section>
<hr>
<section class="container">
	<h2>OpenAL</h2>
	<p><a href="http://www.openal.org/">OpenAL</a> (for "Open Audio Library") is a software interface to audio hardware. The interface consists of a number of functions that allow a programmer to specify the objects and operations in producing high-quality audio output, specifically multichannel output of 3D arrangements of sound sources around a listener.</p>
	
	<p>LWJGL is bundled with <a href="http://kcat.strangesoft.net/openal.html">OpenAL Soft</a>, an LGPL-licensed, cross-platform, software implementation of the OpenAL 3D audio API.</p>
</section>

<? include "footer.php" ?>
