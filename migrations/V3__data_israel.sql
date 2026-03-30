BEGIN;

INSERT INTO articles (header, url_slug, content, latitude, longitude, created_at, updated_at)
VALUES (
	'Israel: A Comprehensive Overview',
	'israel-a-comprehensive-overview',
	$israel$
<h1>Israel: A Comprehensive Overview</h1>

<p>Israel is a country located in the Middle East, along the eastern shore of the Mediterranean Sea. It is a nation with deep historical roots, cultural diversity, and significant geopolitical importance. Despite its relatively small size, Israel has played a major role in global history, religion, and modern technological innovation.</p>

<h2>Geography and Location</h2>

<p>Israel shares borders with Lebanon to the north, Syria to the northeast, Jordan to the east, and Egypt to the southwest. It also has coastlines along the Mediterranean Sea and the Red Sea. The country features diverse landscapes, including coastal plains, mountain ranges, deserts, and fertile valleys.</p>

<table style="border-collapse: collapse; width: 100%;" border="1">
	<colgroup>
		<col style="width: 33.2969%;">
		<col style="width: 33.2969%;">
		<col style="width: 33.2969%;">
	</colgroup>
	<tr>
		<th>Region</th>
		<th>Type of Landscape</th>
		<th>Key Features</th>
	</tr>
	<tr>
		<td>Coastal Plain</td>
		<td>Flat land</td>
		<td>Agriculture, major cities</td>
	</tr>
	<tr>
		<td>Negev Desert</td>
		<td>Arid desert</td>
		<td>Low rainfall, unique wildlife</td>
	</tr>
	<tr>
		<td>Galilee</td>
		<td>Hilly region</td>
		<td>Forests, lakes</td>
	</tr>
</table>

<h2>Historical Background</h2>

<p>The land of Israel has been inhabited for thousands of years and holds significance for several major religions, including Judaism, Christianity, and Islam. Ancient kingdoms such as the Kingdom of Israel and the Kingdom of Judah existed in this region.</p>

<p>Over centuries, the land came under the control of various empires, including the Romans, Byzantines, Ottomans, and the British Empire. In 1948, the modern State of Israel was established, following a United Nations plan to partition the land.</p>

<h2>Population and Society</h2>

<p>Israel has a diverse population composed of Jews, Arabs, and other minority groups. Jewish people form the majority, while Arab citizens, including Muslims, Christians, and Druze, make up a significant minority.</p>

<table style="border-collapse: collapse; width: 100%;" border="1">
	<colgroup>
		<col style="width: 33.2969%;">
		<col style="width: 33.2969%;">
		<col style="width: 33.2969%;">
	</colgroup>
	<tr>
		<th>Group</th>
		<th>Approximate Percentage</th>
		<th>Main Religions</th>
	</tr>
	<tr>
		<td>Jewish</td>
		<td>~74%</td>
		<td>Judaism</td>
	</tr>
	<tr>
		<td>Arab</td>
		<td>~21%</td>
		<td>Islam, Christianity</td>
	</tr>
	<tr>
		<td>Others</td>
		<td>~5%</td>
		<td>Various</td>
	</tr>
</table>

<h2>Economy and Innovation</h2>

<p>Israel has a highly developed economy and is known for its innovation and technological advancements. It is often referred to as the "Startup Nation" due to its large number of startups and research institutions.</p>

<p>Key industries include technology, agriculture, defense, and tourism. The country has made significant contributions in fields such as cybersecurity, medical technology, and water conservation.</p>

<h2>Culture and Religion</h2>

<p>Israel's culture is shaped by its diverse population and historical heritage. Jewish traditions play a central role, but there is also a strong presence of Arab culture. Religious practices and holidays are an important part of daily life.</p>

<p>Major religious sites in Israel attract millions of visitors each year. These include locations sacred to Jews, Christians, and Muslims, making the country a unique center of spiritual significance.</p>

<h2>Political System</h2>

<p>Israel is a parliamentary democracy. The head of state is the President, while the Prime Minister serves as the head of government. The legislative branch is the Knesset, a unicameral parliament.</p>

<table style="border-collapse: collapse; width: 100%;" border="1">
	<colgroup>
		<col style="width: 33.2969%;">
		<col style="width: 33.2969%;">
		<col style="width: 33.2969%;">
	</colgroup>
	<tr>
		<th>Branch</th>
		<th>Role</th>
		<th>Example Institution</th>
	</tr>
	<tr>
		<td>Executive</td>
		<td>Implements laws</td>
		<td>Government</td>
	</tr>
	<tr>
		<td>Legislative</td>
		<td>Makes laws</td>
		<td>Knesset</td>
	</tr>
	<tr>
		<td>Judicial</td>
		<td>Interprets laws</td>
		<td>Supreme Court</td>
	</tr>
</table>

<h2>Challenges and Global Role</h2>

<p>Israel faces a range of challenges, including ongoing regional conflicts and political tensions. The Israeli-Palestinian conflict remains one of the most significant and complex issues in the region.</p>

<p>Despite these challenges, Israel maintains strong international relationships and plays an active role in global affairs, including science, technology, and humanitarian efforts.</p>

<h2>Conclusion</h2>

<p>Israel is a nation with a rich history, diverse society, and dynamic economy. Its unique position in the world makes it both influential and complex. Understanding Israel requires examining its past, present, and the challenges it faces as it continues to develop in the modern era.</p>
	$israel$,
	31.768300,
	35.213700,
	NOW(),
	NOW()
)
ON CONFLICT (url_slug) DO UPDATE
SET
	header = EXCLUDED.header,
	content = EXCLUDED.content,
	latitude = EXCLUDED.latitude,
	longitude = EXCLUDED.longitude,
	updated_at = NOW();

INSERT INTO article_categories (article_id, category_id)
SELECT a.id, c.id
FROM articles a
JOIN categories c ON c.name IN ('Middle East', 'Fact Check', 'Diplomacy')
WHERE a.url_slug = 'israel-a-comprehensive-overview'
ON CONFLICT DO NOTHING;

COMMIT;
