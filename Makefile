BUILDDIR := builds
VERSION := 1.0

.PHONY: all clean distribute
all: bananan.aum lyricwiki.aum fasttorrent.dlm kinozal.dlm nnmclub.dlm pornolab.dlm rutor.dlm rutracker.dlm tpb.dlm youtube.dlm kinozal.host nnmclub.host pornolab.host rutracker.host build

bananan.aum: ${BUILDDIR}
	$(MAKE) -C src/au-bananan && \
	cp src/au-bananan/bananan.aum ${BUILDDIR}

lyricwiki.aum: ${BUILDDIR}
	$(MAKE) -C src/au-lyricwiki && \
	cp src/au-lyricwiki/lyricwiki.aum ${BUILDDIR}

fasttorrent.dlm: ${BUILDDIR}
	$(MAKE) -C src/bt-fasttorrent && \
	cp src/bt-fasttorrent/fasttorrent.dlm ${BUILDDIR}

kinozal.dlm: ${BUILDDIR}
	$(MAKE) -C src/bt-kinozal && \
	cp src/bt-kinozal/kinozal.dlm ${BUILDDIR}

nnmclub.dlm: ${BUILDDIR}
	$(MAKE) -C src/bt-nnmclub && \
	cp src/bt-nnmclub/nnmclub.dlm ${BUILDDIR}

pornolab.dlm: ${BUILDDIR}
	$(MAKE) -C src/bt-pornolab && \
	cp src/bt-pornolab/pornolab.dlm ${BUILDDIR}

rutor.dlm: ${BUILDDIR}
	$(MAKE) -C src/bt-rutor && \
	cp src/bt-rutor/rutor.dlm ${BUILDDIR}

rutracker.dlm: ${BUILDDIR}
	$(MAKE) -C src/bt-rutracker && \
	cp src/bt-rutracker/rutracker.dlm ${BUILDDIR}

tpb.dlm: ${BUILDDIR}
	$(MAKE) -C src/bt-tpb && \
	cp src/bt-tpb/tpb.dlm ${BUILDDIR}

youtube.dlm: ${BUILDDIR}
	$(MAKE) -C src/bt-youtube && \
	cp src/bt-youtube/youtube.dlm ${BUILDDIR}

kinozal.host: ${BUILDDIR}
	$(MAKE) -C src/ht-kinozal && \
	cp src/ht-kinozal/kinozal.host ${BUILDDIR}

nnmclub.host: ${BUILDDIR}
	$(MAKE) -C src/ht-nnmclub && \
	cp src/ht-nnmclub/nnmclub.host ${BUILDDIR}

pornolab.host: ${BUILDDIR}
	$(MAKE) -C src/ht-pornolab && \
	cp src/ht-pornolab/pornolab.host ${BUILDDIR}

rutracker.host: ${BUILDDIR}
	$(MAKE) -C src/ht-rutracker && \
	cp src/ht-rutracker/rutracker.host ${BUILDDIR}

build: ${BUILDDIR}
	cp LICENSE ${BUILDDIR}
	cp README.md ${BUILDDIR}
	cd ${BUILDDIR} && zip -r synox-${VERSION}.zip ./ && mv synox-${VERSION}.zip ../

$(BUILDDIR):
	mkdir -p ${BUILDDIR}

clean:
	rm -rf ${BUILDDIR}
	rm -f synox-${VERSION}.zip
	$(MAKE) clean -C src/au-bananan
	$(MAKE) clean -C src/au-lyricwiki
	$(MAKE) clean -C src/bt-fasttorrent
	$(MAKE) clean -C src/bt-kinozal
	$(MAKE) clean -C src/bt-nnmclub
	$(MAKE) clean -C src/bt-pornolab
	$(MAKE) clean -C src/bt-rutor
	$(MAKE) clean -C src/bt-rutracker
	$(MAKE) clean -C src/bt-tpb
	$(MAKE) clean -C src/bt-youtube
	$(MAKE) clean -C src/ht-kinozal
	$(MAKE) clean -C src/ht-nnmclub
	$(MAKE) clean -C src/ht-pornolab
	$(MAKE) clean -C src/ht-rutracker
