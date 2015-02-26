BUILDDIR := build
SOURCE   := INFO phpQuery.php SynoDLMSearchFastTorrent.php SynoDLMSearchFastTorrentItem.php
PKGNAME  := fasttorrent.dlm
PKGFILES := $(addprefix ${BUILDDIR}/,${SOURCE})

.PHONY: all clean distribute
all: ${PKGNAME}

${PKGNAME}: $(BUILDDIR)
	tar czf ${PKGNAME} -C ${BUILDDIR} .

$(BUILDDIR):
	mkdir -p ${BUILDDIR}
	cp ${SOURCE} ${BUILDDIR}

clean:
	rm -rf ${BUILDDIR}
	rm -f ${PKGNAME}