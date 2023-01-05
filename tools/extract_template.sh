#!/bin/bash
NAME=gdrive

SCRIPT_DIR=$(dirname $0)
PLUGIN_DIR=$(readlink -f "$SCRIPT_DIR/..")
POTFILE=$NAME.pot
LOCALES=$PLUGIN_DIR/locales

cd $PLUGIN_DIR
# Clean existing file
rm -f $LOCALES/$POTFILE && touch $LOCALES/$POTFILE > /dev/null

echo Searching PHP files...
# Append locales from PHP
xgettext `find -type f -name "*.php"` -o $LOCALES/$POTFILE -L PHP --add-comments=TRANS --from-code=UTF-8 --force-po --join-existing \
    --keyword=_n:1,2 --keyword=__s --keyword=__ --keyword=_x:1c,2 --keyword=_sx:1c,2 --keyword=_nx:1c,2,3 --keyword=_sn:1,2 --copyright-holder "TICgal" > /dev/null 2>&1

echo Searching JS files...
# Append locales from JavaScript
xgettext `find -type f -name "*.js"` -o $LOCALES/$POTFILE -L JavaScript --add-comments=TRANS --from-code=UTF-8 --force-po --join-existing \
    --keyword=_n:1,2 --keyword=__ --keyword=_x:1c,2 --keyword=_nx:1c,2,3 \
    --keyword=i18n._n:1,2 --keyword=i18n.__ --keyword=i18n._p:1c,2 \
    --keyword=i18n.ngettext:1,2 --keyword=i18n.gettext --keyword=i18n.pgettext:1c,2 --copyright-holder "TICgal" > /dev/null 2>&1

echo Searching TWIG files...
# Append locales from Twig templates
for file in $(find ./templates -type f -name "*.twig")
do
    # 1. Convert file content to replace "{{ function(.*) }}" by "<?php function(.*); ?>" and extract strings via std input
    # 2. Replace "standard input:line_no" by file location in po file comments
    contents=`cat $file | sed -r "s|\{\{\s*([a-z0-9_]+\(.*\))\s*\}\}|<?php \1; ?>|gi"`
    cat $file | perl -0pe "s/\{\{(.*?)\}\}/<?php \1; ?>/gism" | xgettext - -o $LOCALES/$POTFILE -L PHP --add-comments=TRANS --from-code=UTF-8 --force-po --join-existing \
        --keyword=_n:1,2 --keyword=__ --keyword=_x:1c,2 --keyword=_nx:1c,2,3 --copyright-holder "TICgal"
    sed -i -r "s|standard input:([0-9]+)|`echo $file | sed "s|./||"`:\1|g" $LOCALES/$POTFILE
done

#Update main language
LANG=C msginit --no-translator -i $LOCALES/$POTFILE -l en_GB -o $LOCALES/en_GB.po

### for using tx :
##tx set --execute --auto-local -r GLPI.glpipot 'locales/<lang>.po' --source-lang en_GB --source-file locales/glpi.pot
## tx push -s
## tx pull -a

cd $LOCALES

sed -i "s/SOME DESCRIPTIVE TITLE/GDRIVE Glpi Plugin/" $POTFILE
sed -i "s/FIRST AUTHOR <EMAIL@ADDRESS>, YEAR./TICgal, $(date +%Y)/" $POTFILE
sed -i "s/YEAR/$(date +%Y)/" $POTFILE

localazy upload
localazy download

for a in $(ls *.po); do
	msgmerge -U $a $POTFILE
	msgfmt $a -o "${a%.*}.mo"
done
rm -f *.po~