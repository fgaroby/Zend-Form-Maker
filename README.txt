This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

#########################################################################
Installation guide :
You should have receive with this file :
- a licence.txt file
- an "application" directory
- a "public" directory.

Copy all these files in your server's root. 
I suggest you to create a vhost for your projet called "zfm", if you don't know how, please read the "doc/vhost_howto.txt" file.
Redirect every access to the directory into the public subdirectory, don't forget to enable url rewriting on your apache server as for every Zend Framework Projet.



#########################################################################
Configuration and how to guide :

In the directory "application/configs", there is an application.ini file. 
Some configuration keys are there with the zfm prefix.
You can change some settings there but it's more advised to know what you do before you do it.

Don't forget to correct the path to your Zend Framework library.

In the public directory, you'll find a subdirectory "resources". This is your directory, everything you need to touch should be in here.
I suggest you to put there :
- The font used with the captcha image
- The images you would use in the form
- Your custom decorators files
- Your CSS files needed to display the form while previewing or testing it. All the css files of this directory will be included in these case.

You'll also find a "xml" subdirectory which will contains all the form you'll make with my tool.
And finally, the form_made subdirectory will contains the class generetad by the tool for you.

I hope you'll like this tool, don't hesitate to contact me if you got any question, suggestion, bug, etc... I'll be glad to have some feedback.