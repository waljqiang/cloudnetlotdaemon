local keys = redis.call('keys',KEYS[1].."*")
if(next(keys) ~= nil) then
	return redis.call('del',unpack(keys))
else
	return 0
end