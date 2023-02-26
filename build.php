<?php
declare(strict_types = 1);
require 'lib.php';

ob_start();
$allSources = Source::getAll();
$allContent = Content::getAll();

?>
<!doctype html>
<meta charset=utf-8>
<title>A corpus of Miyazaki Touten’s family collection</title>
<link href="style.css" rel=stylesheet>
<script src="dist/xlsx.mini.min.js"></script>
<body class=client>

<header>
	<a href="https://www.polyu.edu.hk/en/" target=_blank id=logo><img src="images/polyu-logo.png"></a>
	<nav id=nav>
		<a href="index.html">Corpus Search</a>
		<a href="about-us.html">About Us</a>
		<a href="introduction.html">Introduction</a>
		<a href="brush-talk.html">Sinitic Brushtalk</a>
		<a href="publications.html">Our Publications</a>
		<a href="choepu.html">Ch'oe Pu</a>
	</nav>
	<a href="https://www.polyu.edu.hk/en/" target=_blank id=tongue><img src="images/polyu-tongue.png"></a>
</header>

<div class=page-title>
	<div class=center_wrap>
		<div lang=zh-HK>《宮崎滔天家藏：來自日本的中國革命文獻》語料庫</div>
		<div lang=en>A corpus of Miyazaki Touten’s family collection: Documents on Chinese October Revolution from Japan</div>
	</div>
</div>

<div class=content>
	<div class=center_wrap>
		<aside>
			<input type=text id=filter placeholder="關鍵詞">
			<div class=filter_desc>輸入字、詞或拼音</div>
			<div class=update_desc>(欲瀏覽最新版本，請先清除「瀏覽記錄」)</div>
		</aside>

		<main>
			<div id=result></div>
		</main>
	</div>
</div>

<footer id=footer>
	<div class=center_wrap>
		<div id=footer-logo>
			<img src="images/polyu.png" id=logo1>
			<img src="images/cbs.png" id=logo2>
		</div>
		<div id=footer-copyright>
			© 2022 香港理工大學 版權所有
		</div>
		<div id=footer-last-updated>
			最後更新日期：2023年02月26日
		</div>
	</div>
</footer>

<script>
class CorpusSearchResultList extends HTMLElement {
	constructor() {
		super();
		this.initialized = false;
		this.numPerPage = 20;
	}

	setSources(sources) {
		this.sources = sources;
	}

	setData(query, data, elapsedTime) {
		this.page = 1;
		this.query = query;
		this.data = data;
		this.elapsedTime = elapsedTime;
		if (this.initialized) {
			this.render();
		}
	}

	connectedCallback() {
		if (this.initialized) {
			return;
		}
		this.initialized = true;
		this.addEventListener('click', (e) => {
			const pageBtn = e.target.closest('a.search_page');
			if (pageBtn) {
				const page = +pageBtn.dataset.page;
				this.page = page;
				this.render();
			}

			const exportBtn = e.target.closest('button.search_export');
			if (exportBtn) {
				this.exportResults();
			}
		});
		this.innerHTML =
			'<h1>語料檢索</h1>' +
			'<div class=search_result_info></div>' +
			'<div class=search_result_export></div>' +
			'<div class=search_result_list></div>' +
			'<div class=search_result_info></div>' +
			'<div class=search_result_paginate></div>';
		this.render();
	}

	render() {
		const rowIds = this.data;

		const query = this.query;

		const exportDiv = this.querySelectorAll('.search_result_export')[0];
		const info1 = this.querySelectorAll('.search_result_info')[0];
		const info2 = this.querySelectorAll('.search_result_info')[1];
		const list = this.querySelectorAll('.search_result_list')[0];
		const paginate = this.querySelectorAll('.search_result_paginate')[0];

		if (rowIds.length == 0) {
			info1.textContent = '';
			info2.textContent = '';
			list.textContent = 'No results.';
			paginate.innerHTML = '';
			return;
		}

		const numPages = Math.ceil(rowIds.length / this.numPerPage);
		const start = (this.page - 1) * this.numPerPage;
		const end = this.page * this.numPerPage;

		info1.textContent = `現在顯示 ${rowIds.length} 結果中 ${start + 1} 到 ${Math.min(end, rowIds.length)} 項`;
		info2.textContent = `現在顯示 ${rowIds.length} 結果中 ${start + 1} 到 ${Math.min(end, rowIds.length)} 項`;

		exportDiv.innerHTML = '<button type=button class=search_export>下載至 Excel</button>';

		paginate.innerHTML = '';
		list.innerHTML = '';

		if ((this.page - 1) > 0) {
			paginate.insertAdjacentHTML('beforeend', `<a class=search_page data-page="${this.page - 1}">上一頁</a>`);
		} else {
			paginate.insertAdjacentHTML('beforeend', `<a class="search_page disabled">上一頁</a>`);
		}

		let startRange = this.page - 4;
		let endRange = this.page + 4;

		if (this.page > 4 && this.page < numPages - 3) {
			startRange = this.page - 1;
			endRange = this.page + 1;
		} else if (this.page < 5) {
			startRange = 1;
			endRange = 5;
		} else if (this.page > numPages - 4) {
			startRange = numPages - 4;
			endRange = numPages;
		}

		if (startRange < 1) {
			startRange = 1;
		}

		if (endRange > numPages) {
			endRange = numPages;
		}

		if (startRange != 1) {
			paginate.insertAdjacentHTML('beforeend', `<a class=search_page data-page="1">1</a>`);
			if (startRange > 2) {
				paginate.insertAdjacentHTML('beforeend', `<a class="search_page disabled">...</a>`);
			}
		}

		for (let i = startRange; i <= endRange; i++) {
			if (i == this.page) {
				paginate.insertAdjacentHTML('beforeend', `<a class="search_page selected" data-page="${i}">${i}</a>`);
			} else {
				paginate.insertAdjacentHTML('beforeend', `<a class=search_page data-page="${i}">${i}</a>`);
			}
		}

		if (endRange != numPages) {
			if (endRange < numPages - 1) {
				paginate.insertAdjacentHTML('beforeend', `<a class="search_page disabled">...</a>`);
			}
			paginate.insertAdjacentHTML('beforeend', `<a class=search_page data-page="${numPages}">${numPages}</a>`);
		}

		if ((this.page + 1) <= numPages) {
			paginate.insertAdjacentHTML('beforeend', `<a class=search_page data-page="${this.page + 1}">下一頁</a>`);
		} else {
			paginate.insertAdjacentHTML('beforeend', `<a class="search_page disabled">下一頁</a>`);
		}

		rowIds.slice(start, end).forEach(([source_id, sentence_id]) => {
			const sentenceData = content.filter(row => row.source_id == source_id && row.sentence_id == sentence_id);
			const html = document.createElement('corpus-sentence');
			html.setData(query, sentenceData);
			list.appendChild(html);
		});

		// list.insertAdjacentHTML('beforeend', `<div>Query completed in ${this.elapsedTime} ms</div>`);
	}

	exportResults() {
		const aoa = [];
		aoa.push(['出處', '2010頁碼', '2016頁碼', '內文', '內文（關鍵詞）', '內文']);
		this.data.forEach(([source_id, sentence_id]) => {
			const sentenceData = content.filter(row => row.source_id == source_id && row.sentence_id == sentence_id);

			const sourceName = this.sources.find(o => o.id == source_id).name;
			const page2010 = sentenceData.find(row => row.content_type === '2010頁碼').content[0];
			const page2016 = sentenceData.find(row => row.content_type === '2016頁碼').content[0];

			const thisText = this.query;
			const textChunks = sentenceData[0].content[0].split(thisText);
			for (let i = 0; i < textChunks.length - 1; i++) {
				const prevText = textChunks.slice(0, i + 1).join(thisText);
				const nextText = textChunks.slice(i + 1).join(thisText);
				const row = [sourceName, page2010, page2016, prevText, thisText, nextText];
				aoa.push(row);
			}
		});
		const workbook = XLSX.utils.book_new();
		const worksheet = XLSX.utils.aoa_to_sheet(aoa, {});
		XLSX.utils.book_append_sheet(workbook, worksheet, 'BrushTalkConcordance');
		XLSX.writeFile(workbook, "BrushTalkConcordance.xlsx");
	}
}

class CorpusSentence extends HTMLElement {
	constructor() {
		super();
		this.initialized = false;
	}

	setData(query, data) {
		this.query = query;
		this.data = data
	}

	connectedCallback() {
		if (this.initialized) {
			return;
		}
		this.initialized = true;
		this.addEventListener('click', (e) => {
			const button = e.target.closest('.sentence_expand button')
			if (button) {
				const analysis = this.querySelector('.sentence_analysis');
				analysis.hidden = !analysis.hidden;
				if (analysis.firstChild === null) {
					this.renderAnalysis();
				}
			}
		});
		this.render();
	}

	render() {
		const sentenceData = this.data;
		const mainSentence = sentenceData.filter(row => row.content_type === 'sentence');
		let html = '';
		html += '<div class=sentence>';
		html += '<div class=sentence_id>' + mainSentence[0].sentence_id + '</div>';
		html += '<div class=sentence_sentence></div>';
		html += '<div class=sentence_expand><button>顯示資料</button></div>';
		html += '<div class=sentence_analysis hidden></div>';
		html += '</div>';
		this.innerHTML = html;
		this.querySelector('.sentence_sentence').innerHTML = 
			htmlspecialchars(mainSentence[0].content[0]).split(this.query).join('<span class=match>' + this.query + '</span>');
	}

	renderAnalysis() {
		const sentenceData = this.data;

		let maxCols = 0;
		sentenceData.forEach((row) => {
			const len = row.content.map(cell => cell.length ? 1 : 0).join('').replace(/0+$/, '').length;
			maxCols = Math.max(maxCols, len);
		})

		let html = '';
		html += '<table>';
		html += '<col width=200>';
		html += '<tr><th>Type</th><th colspan="' + maxCols + '">Data</th>' + "\r\n";
		sentenceData.forEach((row, i) => {
			html += '<tr>';
			html += '<td>' + htmlspecialchars(row.content_type) + '</td>';
			if (row.content.length === 1) {
				if (row.content_type == 'en2') {
					html += '<td colspan="' + maxCols + '">';
					html += htmlspecialchars(row.content[0]).split(this.query).join('<span class=match>' + this.query + '</span>');
					html += '</td>';
				} else {
					html += '<td colspan="' + maxCols + '">' + htmlspecialchars(row.content[0]) + '</td>';
				}
			} else {
				row.content.forEach(cell => {
					html += '<td>' + htmlspecialchars(cell) + '</td>';
				})
			}
			html += '</tr>' + "\r\n";
		})
		html += '</table>' + "\r\n";

		this.querySelector('.sentence_analysis').innerHTML = html;
	}
}

customElements.define('corpus-search-result-list', CorpusSearchResultList);
customElements.define('corpus-sentence', CorpusSentence);
</script>

<script>
function htmlspecialchars(text) {
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

const sources = <?=json_encode($allSources, JSON_UNESCAPED_UNICODE)?>;
const content = <?=json_encode($allContent, JSON_UNESCAPED_UNICODE)?>;
let lastTimer = 0;

filter.addEventListener('input', (e) => {
	if (lastTimer) window.clearTimeout(lastTimer);
	lastTimer = window.setTimeout(update, 300);
});

function update() {

	const q = filter.value.toLowerCase();

	if (q === '') {
		result.innerHTML = '<h1>語料檢索</h1>' + '請輸入關鍵詞';
		return;
	}

	result.textContent = 'Loading...';

	const startTime = performance.now();
	const applicableContentForSearch = content.filter(row => 
		row.content_type === 'sentence' || 
		row.content_type === 'en2'
		// row.content_type === 'jp2' || 
		// row.content_type === 'en3'
	);
	const rowIds = applicableContentForSearch.filter(row => row.content[0].toLowerCase().includes(q)).map(row => [row.source_id, row.sentence_id]);
	const rowIds2 = new Set(rowIds.map(row => row[0] + '-' + row[1]));
	const rowIds3 = [...rowIds2].map(it => it.split('-'));
	const endTime = performance.now();
	const elapsedTime = endTime - startTime;

	result.textContent = '';
	const resultsList = document.createElement('corpus-search-result-list');
	resultsList.setSources(sources);
	resultsList.setData(q, rowIds3, elapsedTime);
	result.appendChild(resultsList);
}

update();
</script>
<?php

$directories = [
	'build',
	'build/dist',
	'build/images',
	'build/images/brushtalk.files',
];

foreach ($directories as $dir) {
	if (!file_exists($dir)) {
		mkdir($dir);
	}
}

$html = ob_get_clean();
file_put_contents('build/index.html', $html);

$filesToCopy = [
	'.htaccess',
	'about-us.html',
	'brush-talk.html',
	'choepu.html',
	'introduction.html',
	'publications.html',
	'style.css',
	'dist/xlsx.mini.min.js',
	'dist/xlsx.mini.min.map',
	...glob('images/*.jpg'),
	...glob('images/*.png'),
	...glob('images/brushtalk.files/*.jpg'),
	...glob('images/brushtalk.files/*.gif'),
	...glob('images/brushtalk.files/*.png'),
];

$filesToCopy = array_filter($filesToCopy, function($o) {
	return $o !== 'images/cbs_original.png';
});

foreach ($filesToCopy as $file) {
	file_put_contents("build/$file", file_get_contents($file));
}

// Source: https://stackoverflow.com/a/4914807
$rootPath = realpath('build');
$zip = new ZipArchive();
$zip->open('build.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $name => $file) {
    if (!$file->isDir()) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($rootPath) + 1);
        $zip->addFile($filePath, $relativePath);
    }
}

$zip->close();

header("Content-Type: application/zip");
header("Content-Transfer-Encoding: Binary");
header("Content-Length: " . filesize('build.zip'));
header("Content-Disposition: attachment; filename=brushtalk-" . date('YmdHis') . ".zip");
echo file_get_contents('build.zip');
