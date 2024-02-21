https://fossil-scm.org/home/doc/trunk/www/settings.wiki

Using Fossil Settings
Settings control the behaviour of fossil. They are set with the fossil
settings command, or through the web interface in the Settings page in the
Admin section.

For a list of all settings, view the Settings page, or type fossil help
settings from the command line.

Repository settings
Settings are set on a per-repository basis. When you clone a repository, a
subset of settings are copied to your local repository.

If you make a change to a setting on your local repository, it is not synced
back to the server when you push or sync. If you make a change on the server,
you need to manually make the change on all repositories which are cloned from
this repository.

You can also set a setting globally on your local machine. The value will be
used for all repositories cloned to your machine, unless overridden explicitly
in a particular repository. Global settings can be set by using the -global
option on the fossil settings command.

"Versionable" settings
Most of the settings control the behaviour of fossil on your local machine,
largely acting to reflect your preference on how you want to use Fossil, how
you communicate with the server, or options for hosting a repository on the
web.

However, for historical reasons, some settings affect how you work with
versioned files. These are clean-glob, binary-glob, crlf-glob (and its alias
crnl-glob), empty-dirs, encoding-glob, ignore-glob, keep-glob, manifest, and
mimetypes. The most important is ignore-glob which specifies which files
should be ignored when looking for unmanaged files with the extras command.

Because these options can change over time, and the inconvenience of
replicating changes, these settings are "versionable". As well as being able
to be set using the settings command or the web interface, you can create
versioned files in the .fossil-settings subdirectory of the check-out root,
named with the setting name. The contents of the file is the value of the
setting, and these files are checked in, committed, merged, and so on, as with
any other file.

Where a setting is a list of values, such as ignore-glob, you can use a
newline as a separator as well as a comma.

For example, to set the list of ignored files, create a
.fossil-settings/ignore-glob file where each line contains a glob for ignored
files.

If you set the value of a setting using the settings command as well as a
versioned file, the versioned setting will take precedence. A warning will be
displayed.
