BUILDDIR := build
SOURCE   := INFO SynoDLMSearchYouTube.php SynoDLMSearchYouTubeItem.php
PKGNAME  := youtube.dlm
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