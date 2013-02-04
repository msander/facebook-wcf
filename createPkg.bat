@echo off

echo creating files.tar....

cd files
..\tar cf ..\files.tar *
cd ..


echo creating templates.tar....

cd templates
..\tar cf ..\templates.tar *
cd ..

echo creating pkg.tar

tar cf pkg.tar optionals files.tar templates.tar de-informal.xml de.xml en.xml eventlistener.xml install.sql package.xml options.xml LICENSE templates113.diff update.sql update2.sql usercpmenu.xml useroptions.xml

echo cleaning up....
del files.tar
del templates.tar


echo done!