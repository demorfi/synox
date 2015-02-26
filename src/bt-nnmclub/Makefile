BUILDDIR := build
SOURCE   := INFO phpQuery.php SynoDLMSearchNNMClub.php SynoDLMSearchNNMClubItem.php
PKGNAME  := nnmclub.dlm
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