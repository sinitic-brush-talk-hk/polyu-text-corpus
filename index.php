<?php
declare(strict_types = 1);
require 'lib.php';

$allSources = Source::getAll();
$allContent = Content::getAll();

?>
<!doctype html>
<meta charset=utf-8>
<title>User Site</title>
<link href="style.css" rel=stylesheet>
<body class=client>

<div class=content>
	<aside>
		<input type=text id=filter>
	</aside>

	<main>
		<div id=result></div>
	</main>
</div>
<script>
class CorpusSearchResultList extends HTMLElement {
	constructor() {
		super();
		this.initialized = false;
		this.numPerPage = 20;
	}

	setData(data, elapsedTime) {
		this.page = 1;
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
		});
		this.innerHTML =
			'<div class=search_result_info></div>' +
			'<div class=search_result_list></div>' +
			'<div class=search_result_info></div>' +
			'<div class=search_result_paginate></div>';
		this.render();
	}

	render() {
		const rowIds = this.data;

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
			html.setData(sentenceData);
			list.appendChild(html);
		});

		list.insertAdjacentHTML('beforeend', `<div>Query completed in ${this.elapsedTime} ms</div>`);
	}
}

class CorpusSentence extends HTMLElement {
	constructor() {
		super();
		this.initialized = false;
	}

	setData(data) {
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
		html += '<div class=sentence_sentence>' + mainSentence[0].content[0] + '</div>';
		html += '<div class=sentence_expand><button>顯示資料</button></div>';
		html += '<div class=sentence_analysis hidden></div>';
		html += '</div>';
		this.innerHTML = html;
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
				html += '<td colspan="' + maxCols + '">' + htmlspecialchars(row.content[0]) + '</td>';
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

const sources = <?=json_encode($allSources)?>;
const content = <?=json_encode($allContent)?>;
let lastTimer = 0;

filter.addEventListener('input', (e) => {
	if (lastTimer) window.clearTimeout(lastTimer);
	lastTimer = window.setTimeout(update, 300);
});

function update() {

	const q = filter.value.toLowerCase();

	if (q === '') {
		result.textContent = 'Please type something.';
		return;
	}

	result.textContent = 'Loading...';

	const startTime = performance.now();
	const applicableContentForSearch = content.filter(row => 
		row.content_type === 'sentence'
		// row.content_type === 'sentence' || 
		// row.content_type === 'en2' || 
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
	resultsList.setData(rowIds3, elapsedTime);
	result.appendChild(resultsList);
}

update();
</script>
